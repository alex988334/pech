<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\Master;
use common\models\Manager;
use common\models\Klient;
use common\models\MasterSearch;
use common\models\MasterVsZakaz;
use common\models\Zakaz;
use common\models\VidStatusWork;
use common\models\VidDefault;
use common\models\VidRegion;
use common\models\VidStatusZakaz;
use common\models\VidInitializationMaster;
use common\models\VidStatusHistory;
use frontend\models\SignupForm;
use frontend\models\UpdateForm;
use common\models\User;
use common\models\AuthAssignment;
use common\models\ManagerTableGrant;
use common\models\HistoryMaster;
use yii\helpers\ArrayHelper;

class MasterController extends Controller 
{
    const STATUS_ACCEPT = 1;
    const STATUS_ERROR = 0;
    
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
                  //          'index',                              
                            'kabinet', 
                            'vashi-zakazi',
                            'change-password'
                        ],
                        'allow' => true,
                        'roles' => ['master'],
                    ],
                    [                        
                        'actions' => [
                            'index',
                            'view',
                            'create',
                            'update',
                            'delete',                            
                        ],
                        'allow' => true,
                        'roles' => ['manager', 'head_manager'],
                    ],                    
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    
    public function actionIndex()
    {     
        if (Yii::$app->request->get('page') != null) { 
            Yii::$app->session->set('page', Yii::$app->request->get('page'));         
        }
        //Yii::$app->session->setFlash('message', Yii::$app->session->get('page'));
            
        $fields = ManagerTableGrant::findBySql('SELECT t.name, t.alias, `id_table_field`, '
                . '`field_width`, `visibility_field` FROM `manager_table_grant` tg, '
                . ' manager_table t WHERE tg.id_table_field=t.id AND t.parent IN '
                . ' (SELECT id FROM manager_table WHERE name IN ("'. Master::tableName() .'"))'
                . ' AND id_manager=' . Yii::$app->user->getId())->asArray()->all();        

        $searchModel = new MasterSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [                
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'fields' => $fields,
            'massFilters' => Master::getRelationTablesArray(),       
        ]);
    }    
    
    public function actionKabinet()
    {   
        $model = Master::find()                                 
                ->where(['id_master' => Yii::$app->user->getId()])   
                ->with('region', 'statusOnOff', 'statusWork', 'user', 'masterVsZakaz') 
                ->limit(1) 
                ->asArray()
                ->one(); 
        
    /*    $model = Yii::$app->db->createCommand('SELECT ')*/
        
        if (empty($model)) {
            Yii::$app->user->logout();
            Yii::$app->session->setFlash('message', 'Вы не найдены в системе, свяжитесь с менеджером');
            return $this->redirect('/site/login');
        }
        
        return $this->render('kabinet', ['model' => $model]);
    }
        
    public function actionVashiZakazi()
    {       
        $zakaz = MasterVsZakaz::find()
                ->select('id_zakaz')                
                ->where(['id_master' => Yii::$app->user->getId()])             
                ->asArray()
                ->all();    
        if (count($zakaz) > 0){
            $id = ArrayHelper::getColumn($zakaz, 'id_zakaz');
            foreach ($zakaz as $value) { $mass[] = $value['id_zakaz']; }       
            $takeOrders = Zakaz::find()
                    ->where(['id' => $id, 'id_status_zakaz' => VidStatusZakaz::ORDER_REQUEST_TAKE])
                    ->with('vidWork', 'navik', 'statusZakaz', 'shag', 'region', 'klient')
                    ->asArray()
                    ->all();
            $model = Zakaz::find()
                    ->where(['id' => $id, 'id_status_zakaz' => [
                        VidStatusZakaz::ORDER_REQUEST_REJECTION, VidStatusZakaz::ORDER_EXECUTES
                    ]])
                    ->with('vidWork', 'navik', 'statusZakaz', 'shag', 'region', 'klient')
                    ->asArray()
                    ->all();
            /*$executesOrders = Zakaz::find()
                    ->where(['id' => $id, 'id_status_zakaz' => VidStatusZakaz::ORDER_EXECUTES])
                    ->with('vidWork', 'navik', 'statusZakaz', 'shag', 'region', 'klient')
                    ->asArray()
                    ->all();*/
        } else { $takeOrders = $model = []; }
       
        return $this->render('vashiZakazi', ['takeOrders' => $takeOrders, 'model' => $model]);
    }  
    
    
    /**
     * Displays a single Master model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model1 = HistoryMaster::createHistoryModel($id, VidStatusHistory::STATUS_LOOK);       
        
        if ($model1->validate() && $model1->save()) Yii::$app->session->setFlash('message', 'УСПЕХ ЗАПИСИ!');
        else Yii::$app->session->setFlash('message', 'ОШИБКА ЗАПИСИ!');
        
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Master model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SignupForm();
        $model1 = new Master();
        $model1->scenario = Master::SCENARIO_CREATE;
        
        if ($model->load(Yii::$app->request->post())) {
           
            $user = User::find()->select(['id'])->where(['username' => $model->username])->limit(1)->one();
            
            if ($user) {
                $manager = Manager::find()->where(['id_manager' => $user->id])->limit(1)->one();
                $klient = Klient::find()->where(['id_klient' => $user->id])->limit(1)->one();
                $master = Master::find()->where(['id_master' => $user->id])->limit(1)->one();
                
                if ($master || $manager || $klient) {
                    Yii::$app->session->setFlash('message', 'Такой пользователь уже существует');
                    $model->password = '';
                    return $this->render('signup', ['model' => $model]);
                } else {
                    $id = User::find()->select(['id'])->where(['username' => $model->username])->limit(1)->scalar();
                    $model1->id_master = $id;
                    return $this->render('create', ['model' => $model1, 'vid' => Master::getRelationTablesArray()]);                    
                }
            } 
            
            if ($user = $model->signup()) {
                
                $id = User::find()->select(['id'])->where(['username' => $model->username])->limit(1)->scalar();
                Yii::$app->db->createCommand('UPDATE `user` SET `updated_at`=1 WHERE `id`=' . $id)->execute();
                $model1->id_master = $id;
                return $this->render('create', ['model' => $model1, 'vid' => Master::getRelationTablesArray()]);
            }            
        }
        
        if ($model1->load(Yii::$app->request->post())) {
            
            $initialization = VidInitializationMaster::find()->asArray()->limit(1)->one();
            $model1->data_registry = date('Y-m-d');
            $model1->id_region = Yii::$app->session->get('id_region');
            $model1->balans = $initialization['start_balans'];
            $model1->reyting = $initialization['start_reyting'];
            $model1->limit_zakaz = $initialization['limit_zakaz'];
            
            if ($model1->validate() && $model1->save()) {
                
                $role = new AuthAssignment();
                $role->user_id = $model1->id_master;
                $role->item_name = 'master';
                if ($role->validate() && $role->save()) {
                
                    $url = Yii::$app->urlManager->createUrl(['/navik/create', 'id_master' => $model1->id_master]);
                    Yii::$app->session->setFlash('message', 'Мастер создан');
                    
                    $model2 = HistoryMaster::createHistoryModel(
                            Master::find()->select(['id'])->where(['id_master' => $model1->id_master])->scalar(), 
                            VidStatusHistory::STATUS_CREATE);       
                    
                    if ($model2->validate() && $model2->save()) Yii::$app->session->setFlash('message', 'УСПЕХ ЗАПИСИ!');
                    else Yii::$app->session->setFlash('message', 'ОШИБКА ЗАПИСИ!');
                    
                    return $this->redirect($url); 
                } else {
                    Yii::$app->session->setFlash('message', 'Роль мастеру не назначена, '
                        . 'удалите мастера и пересоздайте мастера "№ мастера"=' . $model1->id_master . '');
                    return $this->redirect(['index']);
                }
            } else {
                return $this->render('create', ['model' => $model1, 'vid' => Master::getRelationTablesArray()]);                    
            }  
        }
        $model->password = '';
        return $this->render('signup', ['model' => $model]);
    }

  /*  public function actionNavik()
    {
        $model = new MasterNavikForm();
        
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            
        }
        if (Yii::$app->request->isPost && $id = Yii::$app->request->post('id')) {
            
            
            $model->id_master = $id;
        } else {
            $massId = MasterWorkNavik::find()->select(['id_master'])
                    ->groupBy('id_master')->asArray()->all();
            $massId = implode(', ', $massId);

            $model = Master::findBySql('SELECT id_master, login, imya, familiya '
                    . 'FROM master WHERE id_master NOT IN (' . $massId.')')->all();
        }
        
    }*/
    
    /**
     * Updates an existing Master model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        $role = Yii::$app->session->get('role');
        if ($role == User::HEAD_MANAGER) {
            $model->scenario = Master::SCENARIO_UPDATE_HEAD_MANAGER;
        } else {
            $model->scenario = Master::SCENARIO_UPDATE_MANAGER;                 //  $role == User::MANAGER
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            $model1 = HistoryMaster::createHistoryModel($id, VidStatusHistory::STATUS_CHANGE);       
        
            if ($model1->validate() && $model1->save()) Yii::$app->session->setFlash('message', 'УСПЕХ ЗАПИСИ!');
            else Yii::$app->session->setFlash('message', 'ОШИБКА ЗАПИСИ!');
            
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model, 'vid' => Master::getRelationTablesArray()
        ]);
    }

    /**
     * Deletes an existing Master model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model1 = HistoryMaster::createHistoryModel($id, VidStatusHistory::STATUS_DELETE);       
        
        if ($model1->validate() && $model1->save()) Yii::$app->session->setFlash('message', 'УСПЕХ ЗАПИСИ!');
        else Yii::$app->session->setFlash('message', 'ОШИБКА ЗАПИСИ!');
        
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    /**
     * Finds the Master model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Master the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Master::find()->where('id=:id', [':id' => $id])
                ->with('user')->with('statusOnOff')->with('statusWork')
                ->with('region')->limit(1)->one();
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    /*public static function getRelationTablesArray()
    {
        $vid = [];
        $vid['vidStatusOnOff'] = VidDefault::find()->indexBy('id')->asArray()->all();
        $vid['vidStatusWork'] = VidStatusWork::find()->indexBy('id')->asArray()->all();
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->indexBy('id')->asArray()->all();
        
        return $vid;
    }*/
    
    public function actionChangePassword()
    {
        $model = new UpdateForm();
        
        if ($model->load(Yii::$app->request->post())) {            
            
            if ($model->update()) {
                return $this->redirect('kabinet');
            }
        }        
        
        return $this->render('change-password', ['model' => $model]);
    }
}