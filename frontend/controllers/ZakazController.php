<?php

namespace frontend\controllers;

use Yii;
use common\models\Zakaz;
use common\models\ZakazSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\Master;
use common\models\ClientOrderMaster;
use common\models\MasterVsZakaz;
use common\models\MasterWorkNavik;
use common\models\VidRegion;
use common\models\VidWork;
use common\models\VidNavik;
use common\models\VidStatusZakaz;
use common\models\VidShag;
use common\models\VidOcenka;
use common\models\VidChangeParametr;
use yii\db\Connection;
use yii\db\Transaction;
use common\models\User;
use common\models\ManagerTable;
use common\models\ManagerTableGrant;
use common\models\AuthItem;
use common\models\HistoryZakaz;
use common\models\VidStatusHistory;
use common\models\FileManager;

use yii\helpers\FileHelper;

use frontend\models\ImageForm;
use yii\web\UploadedFile;



/**
 * ZakaziController implements the CRUD actions for Zakazi model.
 */
class ZakazController extends Controller
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
                            'index',                                                        
                            'view',
                            'wer'
                        ],
                        'allow' => true,
                        'roles' => ['master', 'manager', 'head_manager'],
                    ], 
                    [                        
                        'actions' => [                             
                            'vid',
                            'activate-zakaz',
                            'init-diactivate-zakaz'
                        ],
                        'allow' => true,
                        'roles' => ['master'],
                    ], 
                    [                        
                        'actions' => [
                            'create',
                            'delete',
                            'update',
                            'save-fields',
                            'accept-take-order',
                            'repeat',
                            'show-executed-orders',
                            'show-blocked-orders',
                        ],
                        'allow' => true,
                        'roles' => ['manager', 'head_manager'],
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

    public function actionShowExecutedOrders()
    {
        if (Yii::$app->session->get('invisibleExecutedOrders') == FALSE) {
            Yii::$app->session->set('invisibleExecutedOrders', TRUE);
        } else {
            Yii::$app->session->set('invisibleExecutedOrders', FALSE);
        }
        
        return $this->redirect(Yii::$app->request->referrer ?? '/client-order-master/index');
    }
    
    public function actionShowBlockedOrders()
    {
        if (Yii::$app->session->get('invisibleBlockedOrders') == FALSE) {
            Yii::$app->session->set('invisibleBlockedOrders', TRUE);
        } else {
            Yii::$app->session->set('invisibleBlockedOrders', FALSE);
        }
        
        return $this->redirect(Yii::$app->request->referrer ?? '/client-order-master/index');
    }
    
    
    public function actionAcceptTakeOrder()
    {
        if (!Yii::$app->request->isAjax) return;
        
        if ($id = Yii::$app->request->post('id')) {
            try {
                if (Yii::$app->db->createCommand('UPDATE zakaz SET id_status_zakaz=' . VidStatusZakaz::ORDER_EXECUTES
                    . ' WHERE id=' . $id)->execute()) {
                    return json_encode(['status' => self::STATUS_ACCEPT]);
                }                   
            } catch (\Exception $ex) {
                return json_encode(['status' => self::STATUS_ERROR]);
            }
        }
        
        return json_encode(['status' => self::STATUS_ERROR, 's_code' => VidStatusZakaz::ORDER_EXECUTES]);        
    }

    public function actionRepeat()
    {
        if (!Yii::$app->request->isAjax) return json_encode(['status' => self::STATUS_ERROR]);
        
        $id = Yii::$app->request->post('id'); 
        $model = Zakaz::find()->where(['id' => $id])->limit(1)->one();     
        
        if (empty($model)) {
            return json_encode(['status' => self::STATUS_ERROR]);
        }
        
        $model->scenario = Zakaz::SCENARIO_UPDATE_MANAGER;
        $model->id_status_zakaz = VidStatusZakaz::ORDER_EXECUTES;
        
        if ($model->save()) return json_encode(['status' => self::STATUS_ACCEPT]);
        
        return json_encode(['status' => self::STATUS_ERROR, 'id' => $id]);  
    }
    
    public function actionVid() 
    {        
        $session = Yii::$app->session; 
        
        if (Yii::$app->request->isAjax) {         
            $selectedRegion = Yii::$app->request->getBodyParam('selectedRegion');  
          /*  if ($selectedRegion == null) {       
                throw new \yii\web\BadRequestHttpException(400, 'Запрос был поврежден');
                return $this->renderAjax('ind');               
            }   надо както обработать случай не пришедшего на сервер параметра */
            $id_v = VidRegion::find()->where('id=:id', [':id' => $selectedRegion])->limit(1)->scalar();
            if (!$id_v) {                                                       //  проверяем содержимое по белому списку
                $session->setFlash('message', 'Регион не найден');
                return $this->refresh();
            }
            $session->set('selectedRegion', $selectedRegion);                   //  после проверки запись в сессию
        } else {            
            $selectedRegion = $session->get('selectedRegion') ?? $session->get('id_region');
        }
        
        $query = 'SELECT count(id) AS vsego, id_vid_work, SUM(cena) AS cena FROM zakaz '
                . 'WHERE id_region="' . $selectedRegion . '"'
                . ' AND id_status_zakaz IN (SELECT id FROM vid_status_zakaz WHERE visibility_master=1)' 
                . ' GROUP BY id_vid_work';
        
        $zakazi = Zakaz::findBySql($query)->asArray()->all();
        $vidiIzdeliy = VidWork::find()->asArray()->all();
        
        if (Yii::$app->request->isAjax) {            
            return //$this->asJson(['zakazi' => $zakazi, 'vidi' => $vidiIzdeliy]);
                json_encode(['zakaz' => $zakazi, 'vid' => $vidiIzdeliy]);     //  доработать
        } else {            
            $regions = VidRegion::find()->select(['id', 'name'])
                    ->where('parent_id <> 0')->asArray()->all(); 
            
            return $this->render('vid', ['model' => $zakazi,
                    'regions' => $regions, 'selectedRegion' => $selectedRegion,
                    'vidiIzdeliy' => $vidiIzdeliy            
                ]);        
        }
    }
    
    
    public function actionActivateZakaz()
    {       
        $session = Yii::$app->session;    
        
        if (!Yii::$app->request->isPost) {    
            return $this->redirect('/zakaz/index');
        }
        $id = Yii::$app->request->post('id') ?? null;
        $zakaz = Zakaz::find()->where('id=:id', [':id' => $id])->limit(1)->one();  
        $clientOrder = ClientOrderMaster::findOne(['id_order' => $id]);
        
        if (!$zakaz || !$clientOrder) {                                                          //  проверяем присланое пользователем на sql инъекцию
            $session->setFlash('message', 'Нет такой заявки или заявка не связана с клиентом');
            return $this->redirect('/zakaz/index');
        }
        
        $master = Master::find()->select(['limit_zakaz', 'id_region', 'reyting', 'balans'])
                ->where(['id_master' => Yii::$app->user->id])
                ->limit(1)->asArray()->one();        
        $activeZakaz = ClientOrderMaster::find()
                ->where(['id_master' => Yii::$app->user->id])  
                ->andWhere(['<>', 'zakaz.id_status_zakaz', VidStatusZakaz::ORDER_CANCELLED])
                ->andWhere(['<>', 'zakaz.id_status_zakaz', VidStatusZakaz::ORDER_EXECUTED])
                ->joinWith('order')
                ->count();    
        
        if ($master['limit_zakaz'] <= $activeZakaz) {
            $session->setFlash('message', 'Лимит заявок превышен');
            return $this->redirect('/zakaz/index');
        }              
        if ($zakaz->id_status_zakaz != VidStatusZakaz::ORDER_AVAILABLE) {
            $session->setFlash('message', 'Эта заявка занята!');
            return $this->redirect('/zakaz/index');
        }        
        if ($master['id_region'] != $zakaz->id_region) {
            $session->setFlash('message', 'Эта заявка не из вашего региона');
            return $this->redirect('/zakaz/index');
        }        
        if ($master['reyting'] < $zakaz->reyting_start) {
            $session->setFlash('message', 'Ваш рейтинг мал');
            return $this->redirect('/zakaz/index');
        }
        
        $masterNavik = MasterWorkNavik::find()
                ->where(['id_master' => Yii::$app->user->id])
                ->andWhere(['id_vid_work' => $zakaz->id_vid_work])
                ->limit(1)->asArray()->one();
        
        if ($masterNavik == null) {
            $session->setFlash('message', 'Вы не умеете выполнять этот вид работ');
            return $this->redirect('/zakaz/index');     
        }
        
        $vidNavik = VidNavik::find()->select(['id', 'sort'])->indexBy('id')->all();
        
        if ($vidNavik[$masterNavik['id_vid_navik']]['sort'] 
                                < $vidNavik[$zakaz->id_navik]['sort']) {
            $session->setFlash('message', 'Ваш навык для этого вида работ мал');
            return $this->redirect('/zakaz/index');
        }
        $change = VidChangeParametr::find()->limit(1)->asArray()->one();
        $procent = $change['balans_delete'];//->select('value')->limit(1)->scalar();
        $balans = (int)($master['balans'] - $zakaz->cena * $procent / 100);
        
        if ($balans < 0) {
            $session->setFlash('message', 'На балансе не хватает ' . $balans . ' рублей');
            return $this->redirect('/zakaz/index');
        }
       /* $query[] = 'INSERT INTO {{master_vs_zakaz}} '
                . '([[id_master]], [[id_zakaz]]) '              //  , [[time]], [[date]]
                . 'VALUES ("' . Yii::$app->user->id . '", "'. $zakaz->id 
               /* . '", "' . date('H:i:s') . '", "' . date('Y-m-d')  . '")'; */
        
        $query[] = 'UPDATE {{client_order_master}} SET [[id_master]]=' . Yii::$app->user->getId()
                . ' WHERE [[id]]=' . $clientOrder->id;                
        $query[] = 'UPDATE {{zakaz}} SET [[id_status_zakaz]]=' . VidStatusZakaz::ORDER_REQUEST_TAKE
                . ' WHERE [[id]]=' . $zakaz->id;        
        $query[] = 'UPDATE {{master}} SET [[balans]]=' . $balans 
                . ' WHERE [[id_master]]=' . Yii::$app->user->id;
     
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
                
        try {
            foreach ($query as $one) { $connection->createCommand($one)->execute(); }
            $transaction->commit();
            $session->setFlash('message', 'Запрос на утверждении у менеджера'); 
            $session->set('aktivateZakaz', $zakaz->id);                      
        } catch (\Exception $e) {
            $transaction->rollBack();
            $session->setFlash('message', 'Ошибка при выполнении1');
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $session->setFlash('message', 'Ошибка при выполнении2');
            throw $e;
        }       
  
        return $this->redirect('index');
    }
    
    
    public function actionInitDiactivateZakaz()
    {
        $session = Yii::$app->session;
        
        if (!Yii::$app->request->isPost) {
            $session->setFlash('message', 'это не пост!');
            return $this->render('wer');                    
        }
        
        $id = Yii::$app->request->post('id') ?? null;
        $zakaz = Zakaz::find()->where('id=:id', [':id' => $id])->limit(1)->one();  
        $zakaz->scenario = Zakaz::SCENARIO_UPDATE_MASTER;
        
        if (!$zakaz) {                                                          //  проверяем присланое пользователем на sql инъекцию
            $session->setFlash('message', 'Нет такой заявки');
            return $this->redirect('/zakaz/index');
        }
    
        $zakaz->id_status_zakaz = VidStatusZakaz::ORDER_REQUEST_REJECTION;
        
        if ($zakaz->save()) {
            $session->setFlash('message', 'Запрос на подтверждении менеджера');
            return $this->redirect('/master/vashi-zakazi');
        } else {
            $session->setFlash('message', 'Ошибка при выполнении команды, повторите действие');
            return $this->render('wer');
        }
    }
    
    /**
     * Lists all Zakazi models.
     * @return mixed
     */
    public function actionIndex()
    {     
        $role = Yii::$app->session->get('role');
        
        if (Yii::$app->request->get('page') != null) { 
            Yii::$app->session->set('page', Yii::$app->request->get('page'));         
        }
        
        $searchModel = new ZakazSearch();  
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($role == AuthItem::MANAGER || $role == AuthItem::HEAD_MANAGER) {
            $fields = ManagerTableGrant::findBySql('SELECT t.name, t.alias, `id_table_field`, '
                    . '`field_width`, `visibility_field` FROM `manager_table_grant` tg, '
                    . ' manager_table t WHERE tg.id_table_field=t.id AND t.parent IN '
                    . ' (SELECT id FROM manager_table WHERE name IN ("'. Zakaz::tableName() .'"))'
                    . ' AND id_manager=' . Yii::$app->user->getId())->asArray()->all();
        } else {
            $fields = null;
        }
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,    
            'fields' => $fields,
            'massFilters' => Zakaz::getRelationTablesArray()
        ]); 
    }

    /**
     * Displays a single Zakazi model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        $model1 = HistoryZakaz::createHistoryModel($id, VidStatusHistory::STATUS_LOOK);       
        
     //   if (
                $model1->validate(); 
                //&& 
                $model1->save();
                /*) Yii::$app->session->setFlash('message', 'УСПЕХ ЗАПИСИ!');
        else Yii::$app->session->setFlash('message', 'ОШИБКА ЗАПИСИ!');*/
                
        if ($model->image != null && $model->image != '') {
            $model->image = '/' . FileManager::FILES . '/' 
                    . FileManager::ADDRESS_ORDERS . '/' . $model->image; 
        }
        
        return $this->render('view', ['model' => $model, 'model1' => $model1]);
    }

    /**
     * Creates a new Zakazi model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Zakaz();        
        $model->scenario = Zakaz::SCENARIO_CREATE;
        
        $region = VidRegion::findOne(['id' => Yii::$app->session->get('id_region')]);
        $model->id_region = Yii::$app->session->get('id_region');
        $model->dolgota = $region->dolgota;
        $model->shirota = $region->shirota;
        
        if ($model->load(Yii::$app->request->post())) {
            
            if (!$model->shirota || !$model->dolgota) {
                $region = VidRegion::findOne(['id' => Yii::$app->session->get('id_region')]);
                $model->dolgota = $region->dolgota;
                $model->shirota = $region->shirota;
            }
            $model->data_registry = date('Y-m-d');
            $model->shirota_change = rand(-6000, 6000)/1000000 + $model->shirota;
            $model->dolgota_change = rand(-6000, 6000)/1000000 + $model->dolgota;
            $model->id_region = Yii::$app->session->get('id_region');  
            $id = $model->findBySql('SELECT MAX(id) FROM zakaz')->scalar();
            $model->id = $id + 1;
            
            if ($model->save()) {
                
                Yii::$app->session->set('createZakaz', $model->id);                //  запись данных в сессию
                Yii::$app->session->setFlash('message', $model->id);               //  для последующего присваивания заявки клиенту
                
                $model2 = HistoryZakaz::createHistoryModel($model->id, VidStatusHistory::STATUS_CREATE);       
                    
                if ($model2->validate() && $model2->save()) Yii::$app->session->setFlash('message', 'УСПЕХ ЗАПИСИ!');
                else Yii::$app->session->setFlash('message', 'ОШИБКА ЗАПИСИ!');
                
                return $this->redirect(['/client-order-master/create-client-order', 'id_order' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model, 'vid' => Zakaz::getRelationTablesArray()
        ]);
    }

    /**
     * Updates an existing Zakazi model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $path = FileManager::FILES . '/' . FileManager::ADDRESS_ORDERS . '/';
        if (!file_exists($path)) {
            FileHelper::createDirectory($path);
        }
        
        $model1 = new ImageForm();
        $model = $this->findModel($id);
        
        $role = Yii::$app->session->get('role');
        if ($role == User::HEAD_MANAGER) {
            $model->scenario = Zakaz::SCENARIO_UPDATE_HEAD_MANAGER;
        } else {
            $model->scenario = Zakaz::SCENARIO_UPDATE_MANAGER;                 //  $role == User::MANAGER
        }
        
        $name = null;
        
        if ($model1->load(Yii::$app->request->post())){
            $file = Zakaz::find()->select(['image'])->where('id=:id', [':id' => $model1->id])->limit(1)->one();
            
            try {
                \yii\helpers\FileHelper::unlink($path . $file->image);
            } catch (yii\base\ErrorException $e){
                Yii::$app->session->setFlash('message', 'Старый файл изображения не найден');
            }
           
            $model1->image_file = UploadedFile::getInstance($model1, 'image_file');
            $name = $model1->saveFile(); 
            if ($name) {
                $model->id = $model1->id;
                $model->image = $name;
                $model->save();
                $model->image = $model->image;
                $model2 = HistoryZakaz::createHistoryModel($id, VidStatusHistory::STATUS_CHANGE);       
        
                if ($model2->validate() && $model2->save()) Yii::$app->session->setFlash('message', 'УСПЕХ ЗАПИСИ!');
                else Yii::$app->session->setFlash('message', 'ОШИБКА ЗАПИСИ!');
            }
        } elseif ($model->load(Yii::$app->request->post())) {
            
            $old = $this->findModel($id);
            
            if ($old->shirota != $model->shirota || $old->dolgota != $model->dolgota) {
                $model->shirota_change = rand(-6000, 6000)/1000000 + $model->shirota;
                $model->dolgota_change = rand(-6000, 6000)/1000000 + $model->dolgota;
            }
            
            if ($old->id_status_zakaz == VidStatusZakaz::ORDER_EXECUTES 
                    || $old->id_status_zakaz == VidStatusZakaz::ORDER_REQUEST_REJECTION
                    || $old->id_status_zakaz == VidStatusZakaz::ORDER_EXECUTED) {
                
                $model->id_status_zakaz = $old->id_status_zakaz;
                Yii::$app->session->setFlash('message', 'Запрещено менять статус заявки если '
                        . 'она выполняется, или уже выполнена, или идет запрос отказа от заявки, сначала удалите связку в таблице "Мастера и заявки"');
            } elseif (//$model->id_status_zakaz == VidStatusZakaz::ORDER_EXECUTES || 
                    $model->id_status_zakaz == VidStatusZakaz::ORDER_REQUEST_REJECTION 
                    || $model->id_status_zakaz == VidStatusZakaz::ORDER_REQUEST_TAKE) {
                $model->id_status_zakaz = $old->id_status_zakaz;
                Yii::$app->session->setFlash('message', 'Запрещено менять статус заявки на "выполняется", или "запрос отказа", или "запрос взятия"');
            }
            
            if ($name) {
                $model->image = $name;
            }
            if ($model->save()) {
                $model2 = HistoryZakaz::createHistoryModel($id, VidStatusHistory::STATUS_CHANGE);       
        
                if ($model2->validate() && $model2->save()) Yii::$app->session->setFlash('message', 'УСПЕХ ЗАПИСИ!');
                else Yii::$app->session->setFlash('message', 'ОШИБКА ЗАПИСИ!');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        
        return $this->render('update', [
            'model' => $model, 
            'model1' => $model1,
            'vid' => Zakaz::getRelationTablesArray()
        ]);
    }
   /* 
    public static function getRelationTablesArray()
    {
        $vid = [];
        $vid['vidNavik'] = VidNavik::find()->indexBy('id')->asArray()->all();        
        $vid['vidStatusZakaz'] = VidStatusZakaz::find()->select(['id', 'name'])->indexBy('id')->asArray()->all();        
        $vid['vidShag'] = VidShag::find()->indexBy('id')->asArray()->all();
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->indexBy('id')->asArray()->all();
        
        $vid['vidWork'] = VidWork::find()->indexBy('id')->asArray()->all();        
        $vid['vidOcenka'] = VidOcenka::find()->indexBy('id')->asArray()->all();
        
        return $vid;
    }
*/
    /**
     * Deletes an existing Zakazi model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model1 = HistoryZakaz::createHistoryModel($id, VidStatusHistory::STATUS_DELETE);       
        
      //  if (
                $model1->validate();
           //     && 
                $model1->save();
         //       ) Yii::$app->session->setFlash('message', 'УСПЕХ ЗАПИСИ!');
     //   else Yii::$app->session->setFlash('message', 'ОШИБКА ЗАПИСИ!');
        
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Zakazi model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Zakazi the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {     
        //   Голимая иньекция!!!!!  
        $role = Yii::$app->session->get('role');
        if ($role == User::MASTER) {
            $model = Zakaz::find()
                    ->select([
                        'zakaz.id', 'id_vid_work', 'id_navik', 'zakaz.name', 'opisanie', 
                        'reyting_start', 'gorod', 'poselok', 'ulica', 'cena',
                        'id_status_zakaz', 'data_registry', 'data_start', 
                        'data_end', 'id_region', 'image', 'dolgota_change', 'shirota_change'
                    ])
                    ->where('zakaz.id=:id', [':id' => $id])
                    ->joinWith('navik')->joinWith('statusZakaz')
                    //->joinWith('shag')
                    ->joinWith('region')->joinWith('vidWork')                    
                    ->limit(1)
                    ->one();  
        } elseif ($role == User::MANAGER || $role == User::HEAD_MANAGER) {
            $model = Zakaz::find()
                    ->where('zakaz.id=:id', [':id' => $id])
                    ->joinWith('navik')->joinWith('statusZakaz')
                    ->joinWith('shag')
                    ->joinWith('ocenka')
                    ->joinWith('region')->joinWith('vidWork')
                    ->limit(1)
                    ->one();  
        }
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }   
}
