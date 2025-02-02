<?php

namespace frontend\controllers;

use Yii;
use common\models\HistoryKlient;
use common\models\HistoryKlientSearch;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use frontend\models\SignupForm;
use common\models\User;
use common\models\Master;
use common\models\Manager;
use common\models\AuthAssignment;
use common\models\VidDefault;
use common\models\VidRegion;
use common\models\ManagerTableGrant;
use common\models\VidStatusHistory;
use common\models\Klient;

/**
 * HistoryKlientController implements the CRUD actions for HistoryKlient model.
 */
class HistoryKlientController extends Controller
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
     * Lists all HistoryKlient models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->request->get('page') != null) { 
            Yii::$app->session->set('page', Yii::$app->request->get('page'));         
        }
        
        $searchModel = new HistoryKlientSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $fields = ManagerTableGrant::findBySql('SELECT t.name, t.alias, `id_table_field`, '
                . '`field_width`, `visibility_field` FROM `manager_table_grant` tg, '
                . ' manager_table t WHERE tg.id_table_field=t.id AND t.parent IN '
                . ' (SELECT id FROM manager_table WHERE name IN ("'. HistoryKlient::tableName() .'"))'
                . ' AND id_manager=' . Yii::$app->user->getId())->asArray()->all();  
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'fields' => $fields,
            'massFilters' => HistoryKlient::getRelationTablesArray()
        ]);
    }
    
    public function actionRecovery()
    {
        if ($id = Yii::$app->request->post('id')) {
            
            $hKlient = HistoryKlient::findOne(['id' => $id]);

            $klient = new Klient();
            $klient->scenario = Master::SCENARIO_RECOVERY;

            $klient->setAttributes($hKlient->attributes);
            $klient->id = '';
            
            try {
                if ($klient->validate() && $klient->save()) {
                    Yii::$app->session->setFlash('message', 'Успех! №' . $klient->id);
                } else {
                    Yii::$app->session->setFlash('message', 'Error! №' . $klient->id . ', причина - ' . current(current($klient->errors)));
                }
            } catch (\yii\db\IntegrityException $ex) {
                Yii::$app->session->setFlash('message', 'Error! №' . $klient->id . ', причина - ' . $ex->getMessage());
            }            
        }
        
        return $this->redirect(['index', 'page' => Yii::$app->session->get('page') ?? '1']);
    }
    
    
   /* protected function getRelationTablesArray()
    {
        $vid = [];        
        $vid['vidStatusHistory'] = VidStatusHistory::find()->select(['id', 'name'])->asArray()->all();          
        $vid['vidStatusOnOff'] = VidDefault::find()->select(['id', 'name'])->asArray()->all();        
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->asArray()->all();
        
        return $vid;
    }*/
}
