<?php

namespace frontend\controllers;

use Yii;
use common\models\HistoryMaster;
use common\models\HistoryMasterSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\VidStatusHistory;
use yii\filters\AccessControl;
use common\models\ManagerTableGrant;
use common\models\VidDefault;
use common\models\VidStatusWork;
use common\models\VidRegion;
use common\models\Master;

/**
 * HistoryMasterController implements the CRUD actions for HistoryMaster model.
 */
class HistoryMasterController extends Controller
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
     * Lists all HistoryMaster models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->request->get('page') != null) { 
            Yii::$app->session->set('page', Yii::$app->request->get('page'));         
        }
        
        $searchModel = new HistoryMasterSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $fields = ManagerTableGrant::findBySql('SELECT t.name, t.alias, `id_table_field`, '
                . '`field_width`, `visibility_field` FROM `manager_table_grant` tg, '
                . ' manager_table t WHERE tg.id_table_field=t.id AND t.parent IN '
                . ' (SELECT id FROM manager_table WHERE name IN ("'. HistoryMaster::tableName() .'"))'
                . ' AND id_manager=' . Yii::$app->user->getId())->asArray()->all();  
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'fields' => $fields,
            'massFilters' => HistoryMaster::getRelationTablesArray()
        ]);
    }
    
    public function actionRecovery()
    {
        if ($id = Yii::$app->request->post('id')) {
            
            $hMaster = HistoryMaster::findOne(['id' => $id]);

            $master = new Master();
            $master->scenario = Master::SCENARIO_RECOVERY;

            $master->setAttributes($hMaster->attributes);
            $master->id = '';
            
            try {
                if ($master->validate() && $master->save()) {
                    Yii::$app->session->setFlash('message', 'Успех! №' . $master->id);
                } else {
                    Yii::$app->session->setFlash('message', 'Error! №' . $master->id . ', причина - ' . current(current($master->errors)));
                }
            } catch (\yii\db\IntegrityException $ex) {
                Yii::$app->session->setFlash('message', 'Error! №' . $master->id . ', причина - ' . $ex->getMessage());
            }            
        }
        
        return $this->redirect(['index', 'page' => Yii::$app->session->get('page') ?? '1']);
    }

   /* protected function getRelationTablesArray()
    {
        $vid = [];
        $vid['vidStatusHistory'] = VidStatusHistory::find()->asArray()->all();
        $vid['vidStatusOnOff'] = VidDefault::find()->asArray()->all();
        $vid['vidStatusWork'] = VidStatusWork::find()->asArray()->all();
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->asArray()->all();
        
        return $vid;
    }*/
   
}
