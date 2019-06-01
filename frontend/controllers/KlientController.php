<?php

namespace frontend\controllers;

use Yii;
use common\models\Klient;
use common\models\KlientSearch;
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
use common\models\HistoryKlient;
use common\models\VidStatusHistory;

/**
 * KlientController implements the CRUD actions for Klient model.
 */
class KlientController extends Controller
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
                            'index',
                            'view',
                            'create',
                            'update',
                            'delete',
                            'logout',
                        ],
                        'allow' => true,
                        'roles' => ['head_manager', 'manager'],
                    ],                    
                    [                        
                        'actions' => [
                            'contact',
                        ],
                        'allow' => true,
                        'roles' => ['master'],
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
     * Lists all Klient models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->request->get('page') != null) { 
            Yii::$app->session->set('page', Yii::$app->request->get('page'));         
        }
        
        $searchModel = new KlientSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        $fields = ManagerTableGrant::findBySql('SELECT t.name, t.alias, `id_table_field`, '
                . '`field_width`, `visibility_field` FROM `manager_table_grant` tg, '
                . ' manager_table t WHERE tg.id_table_field=t.id AND t.parent IN '
                . ' (SELECT id FROM manager_table WHERE name IN ("'. Klient::tableName() .'"))'
                . ' AND id_manager=' . Yii::$app->user->getId())->asArray()->all();  
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'fields' => $fields,
            'massFilters' => Klient::getRelationTablesArray()
        ]);
    }
    
    
    public function actionContact($id)
    {        
        if (isset($id) && is_array($id)) {
            foreach ($id as $one) {                
                if (!is_int($one)) { 
                    Yii::$app->session->setFlash('message', 'Не задан параметр');
                    throw new NotFoundHttpException('The requested page does not exist.');
                }
            }
            $id = implode(', ', $id);            
        } 
        
        $model = Klient::find()->select(['imya', 'familiya', 'phone'])
                ->where('id_klient IN (' . $id . ')')
                ->asArray()->all();
        
        return json_encode($model);        
    }
    

    /**
     * Displays a single Klient model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        $model1 = HistoryKlient::createHistoryModel($id, VidStatusHistory::STATUS_LOOK);       
        
        if ($model1->validate() && $model1->save()) Yii::$app->session->setFlash('message', 'УСПЕХ ЗАПИСИ!');
        else Yii::$app->session->setFlash('message', 'ОШИБКА ЗАПИСИ!');
        
        return $this->render('view', [
            'model' => $model,         
        ]);
    }    
    

    /**
     * Creates a new Klient model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    
    /**
     * Creates a new Master model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SignupForm();
        $model1 = new Klient();
        $model1->scenario = Klient::SCENARIO_CREATE;
        
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
                    $model1->id_klient = $id;
                    return $this->render('create', ['model' => $model1]);                    
                }
            } 
            
            if ($user = $model->signup()) {
                
                $id = User::find()->select(['id'])->where(['username' => $model->username])->limit(1)->scalar();
                Yii::$app->db->createCommand('UPDATE `user` SET `updated_at`=1 WHERE `id`=' . $id)->execute();
                $model1->id_klient = $id;
                return $this->render('create', ['model' => $model1]);
            }            
        } 
        
        if ($model1->load(Yii::$app->request->post())) {
            
            $model1->id_region = Yii::$app->session->get('id_region');
       
            if ($model1->validate() && $model1->save()) {
                
                $role = new AuthAssignment();
                $role->user_id = $model1->id_klient;
                $role->item_name = 'klient';
                
                if ($role->validate() && $role->save()) {
                    Yii::$app->session->setFlash('message', 'Клиент создан');                    
                    
                    $model2 = HistoryKlient::createHistoryModel(
                            Klient::find()->select(['id'])->where(['id_klient' => $model1->id_klient])->scalar(), 
                            VidStatusHistory::STATUS_CREATE);       
                    
                    if ($model2->validate() && $model2->save()) Yii::$app->session->setFlash('message', 'УСПЕХ ЗАПИСИ!');
                    else Yii::$app->session->setFlash('message', 'ОШИБКА ЗАПИСИ!');
                    
                } else {
                    Yii::$app->session->setFlash('message', 'Роль клиенту не назначена, '
                        . 'удалите и пересоздайте клиента "№ клиента"=' . $model1->id_klient . '');
                }                
                return $this->redirect(['index']);
                
            } else {
                return $this->render('create', ['model' => $model1]);                    
            }
        }
        $model->password = '';
        return $this->render('signup', ['model' => $model]);
    }
    
   /* public function actionCreate()
    {
        $model = new Klient();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Klient model.
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
            $model->scenario = Klient::SCENARIO_UPDATE_HEAD_MANAGER;
        } else {
            $model->scenario = Klient::SCENARIO_UPDATE_MANAGER;                 //  $role == User::MANAGER
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            $model1 = HistoryKlient::createHistoryModel($id, VidStatusHistory::STATUS_CHANGE);       
        
            if ($model1->validate() && $model1->save()) Yii::$app->session->setFlash('message', 'УСПЕХ ЗАПИСИ!');
            else Yii::$app->session->setFlash('message', 'ОШИБКА ЗАПИСИ!');
            
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model, 'vid' => Klient::getRelationTablesArray()
        ]);
    }

    /**
     * Deletes an existing Klient model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model1 = HistoryKlient::createHistoryModel($id, VidStatusHistory::STATUS_DELETE);       
        
        if ($model1->validate() && $model1->save()) Yii::$app->session->setFlash('message', 'УСПЕХ ЗАПИСИ!');
        else Yii::$app->session->setFlash('message', 'ОШИБКА ЗАПИСИ!');
        
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Klient model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Klient the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Klient::find()
                ->select([
                    'id' => 'klient.id', 'id_klient', 'imya', 'familiya', 'otchestvo', 
                    'vozrast', 'id_status_on_off', 'phone', 'reyting', 'balans', 'id_region'
                ])->where(['id' => $id])
                ->with('region')->with('statusOnOff')->with('user')->limit(1)->one();
        
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
   /* public static function getRelationTablesArray()
    {
        $vid = [];        
        $vid['vidStatusOnOff'] = VidDefault::find()->select(['id', 'name'])->asArray()->all();        
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->indexBy('id')->asArray()->all();
        
        return $vid;
    }*/
}
