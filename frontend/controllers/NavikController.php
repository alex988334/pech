<?php

namespace frontend\controllers;

use Yii;
use common\models\MasterWorkNavik;
use common\models\MasterWorkNavikSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\VidWork;
use common\models\VidNavik;
use common\models\Master;
use common\models\ManagerTableGrant;

/**
 * NavikController implements the CRUD actions for MasterWorkNavik model.
 */
class NavikController extends Controller
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
                        'actions' => [                            
                            'create',
                            'update',
                            'delete',                            
                        ],
                        'allow' => true,
                        'roles' => ['manager', 'head_manager'],
                    ],
                    [
                        'actions' => [
                            'index',                                                  
                        ],
                        'allow' => true,
                        'roles' => ['manager', 'head_manager', 'master']
                    ]
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
     * Lists all MasterWorkNavik models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MasterWorkNavikSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (Yii::$app->request->get('page') != null) { 
            Yii::$app->session->set('page', Yii::$app->request->get('page'));         
        }
        $fields = ManagerTableGrant::findBySql('SELECT t.name, t.alias, `id_table_field`, '
                . '`field_width`, `visibility_field` FROM `manager_table_grant` tg, '
                . ' manager_table t WHERE tg.id_table_field=t.id AND t.parent IN '
                . ' (SELECT id FROM manager_table WHERE name IN ("'. MasterWorkNavik::tableName() .'"))'
                . ' AND id_manager=' . Yii::$app->user->getId())->asArray()->all();
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'fields' => $fields,
            'massFilters' => $this->getRelationTablesArray()
        ]);
    }

    /**
     * Displays a single MasterWorkNavik model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new MasterWorkNavik model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MasterWorkNavik();
        $vid = $this->getRelationTablesArray();
        
        if (Yii::$app->request->isGet && Yii::$app->request->get('id_master')) { 
            $model->id_master = Yii::$app->request->get('id_master');
            return $this->render('create', ['model' => $model, 'vid' => $vid]);          
        } 
        
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            Yii::$app->session->setFlash('message', 'Навык успешно создан');
        }
        
        /*if ($model->id_master) {
            $total = MasterWorkNavik::find()->where('id_master=:id_master', [':id_master' => $model->id_master])->count();
            $totalWork = count($vid['vidWork']);
            if ($total >= $totalWork) {
                Yii::$app->session->setFlash('message', 'У этого мастера созданы все навыки');                
            } 
        } */
        $massMaster = Yii::$app->db->createCommand('SELECT m.id_master FROM master m LEFT JOIN master_work_navik wn '
                    . ' ON m.id_master=wn.id_master WHERE (SELECT COUNT(id_master) FROM master_work_navik mv '
                    . ' WHERE mv.id_master=m.id_master)<(SELECT COUNT(id) FROM vid_work) AND id_region=' 
                    . Yii::$app->session->get('id_region') . ' GROUP BY m.id_master')->queryAll();
        
        return $this->render('create', ['model' => $model, 'vid' => $vid, 'massMaster' => $massMaster]);
    }

    /**
     * Updates an existing MasterWorkNavik model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('message', 'Навык успешно изменен');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model, 'vid' => $this->getRelationTablesArray()
        ]);
    }

    /**
     * Deletes an existing MasterWorkNavik model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the MasterWorkNavik model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return MasterWorkNavik the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (Yii::$app->session->get('role') == 'master') {
            $model = MasterWorkNavik::find()->where('id=:id', [':id' => $id])
                    ->with('vidWork')->with('vidNavik')->all();
        } else {
            $model = MasterWorkNavik::find()->where('id=:id', [':id' => $id])
                    ->with('vidWork')->with('vidNavik')->limit(1)->one();
        }
        
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    protected function getRelationTablesArray()
    {
        $vid = [];
        $vid['vidWork'] = VidWork::find()->asArray()->all();
        $vid['vidNavik'] = VidNavik::find()->asArray()->all();
        
        return $vid;
    }
}
