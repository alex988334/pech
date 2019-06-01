<?php

namespace frontend\controllers;

use Yii;
use common\models\HistoryZakaz;
use common\models\HistoryZakazSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\VidStatusHistory;
use yii\filters\AccessControl;
use common\models\ManagerTableGrant;
use common\models\VidNavik;
use common\models\VidWork;
use common\models\VidRegion;
use common\models\VidOcenka;
use common\models\VidStatusZakaz;
use common\models\VidShag;
use common\models\Zakaz;

/**
 * HistoryZakazController implements the CRUD actions for HistoryZakaz model.
 */
class HistoryZakazController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),             
                //'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['error'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [                        
                        'actions' => ['index', 'create', 'recovery'],
                        'allow' => true,
                        'roles' => ['head_manager', 'manager'],
                    ],                
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all HistoryZakaz models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->request->get('page') != null) { 
            Yii::$app->session->set('page', Yii::$app->request->get('page'));         
        }
        
        $searchModel = new HistoryZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $fields = ManagerTableGrant::findBySql('SELECT t.name, t.alias, `id_table_field`, '
                . '`field_width`, `visibility_field` FROM `manager_table_grant` tg, '
                . ' manager_table t WHERE tg.id_table_field=t.id AND t.parent IN '
                . ' (SELECT id FROM manager_table WHERE name IN ("'. HistoryZakaz::tableName() .'"))'
                . ' AND id_manager=' . Yii::$app->user->getId())->asArray()->all();  
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'fields' => $fields,
            'massFilters' => HistoryZakaz::getRelationTablesArray()
        ]);
    }
    
    public function actionRecovery()
    {
        if ($id = Yii::$app->request->post('id')) {
            
            $hZakaz = HistoryZakaz::findOne(['id' => $id]);

            $order = new Zakaz();
            $order->scenario = Zakaz::SCENARIO_RECOVERY;

            $order->setAttributes($hZakaz->attributes);
            if ($order->validate() && $order->save()) {
                Yii::$app->session->setFlash('message', 'Успех! №' . $order->id);
            } else {
                Yii::$app->session->setFlash('message', 'Error! №' . $order->id . ', причина - ' . current(current($order->errors)));
            }
        }
        
        return $this->redirect(['index', 'page' => Yii::$app->session->get('page') ?? '1']);
    }
    
    /*public static function getRelationTablesArray()
    {
        $vid = [];
        $vid['vidStatusHistory'] = VidStatusHistory::find()->asArray()->all();   
        $vid['vidNavik'] = VidNavik::find()->asArray()->all();        
        $vid['vidStatusZakaz'] = VidStatusZakaz::find()->select(['id', 'name'])->asArray()->all();        
        $vid['vidShag'] = VidShag::find()->asArray()->all();
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->asArray()->all();
        
        $vid['vidWork'] = VidWork::find()->asArray()->all();        
        $vid['vidOcenka'] = VidOcenka::find()->asArray()->all();
        
        return $vid;
    }*/
}
