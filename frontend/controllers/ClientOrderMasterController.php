<?php

namespace frontend\controllers;

use Yii;
use common\models\ClientOrderMaster;
use common\models\ClientOrderMasterSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use common\models\AuthItem;
use common\models\ManagerTableGrant;
use common\models\ManagerTable;
use common\models\Klient;
use common\models\Zakaz;
use common\models\Master;
use common\models\VidChangeParametr;
use common\models\VidStatusZakaz;

/**
 * ClientOrderMasterController implements the CRUD actions for ClientOrderMaster model.
 */
class ClientOrderMasterController extends Controller
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
                            'index', 'view', 'create', 'update', 'delete', 'delete-master',
                            'create-client-order', 'create-order-master', 'order-end',                            
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
                    'delete-master' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all ClientOrderMaster models.
     * @return mixed
     */
    public function actionIndex()
    {
        $role = Yii::$app->session->get('role');
        
        if (Yii::$app->request->get('page') != null) { 
            Yii::$app->session->set('page', Yii::$app->request->get('page'));         
        }
        
        $searchModel = new ClientOrderMasterSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        if ($role == AuthItem::MANAGER || $role == AuthItem::HEAD_MANAGER) {
            $fields = ManagerTableGrant::findBySql('SELECT t.name, t.alias, id_table_field, t.parent, t.clone_by, '
                    . 'field_width, visibility_field FROM manager_table_grant tg, '
                    . ' manager_table t WHERE tg.id_table_field=t.id AND t.parent IN '
                    . ' (SELECT id FROM manager_table WHERE name IN ("'. ClientOrderMaster::tableName() .'"))'
                    . ' AND id_manager=' . Yii::$app->user->getId())->asArray()->all();
        } else {
            $fields = null;
        }
        
        $tablesId = ManagerTable::find()
                ->where(['name' => [Klient::tableName(), Zakaz::tableName(), Master::tableName()]])
                ->indexBy('id')->asArray()->all();
        foreach ($tablesId as $value) {
            if ($value['name'] == Klient::tableName()) $tablesId[$value['id']]['connection'] = 'client';
            if ($value['name'] == Zakaz::tableName()) $tablesId[$value['id']]['connection'] = 'order';
            if ($value['name'] == Master::tableName()) $tablesId[$value['id']]['connection'] = 'master';
        }        
                
        $filters = ClientOrderMaster::getRelationTablesArray();
        
        return $this->render('index', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider,    
            'fields' => $fields, 'tablesId' => $tablesId, 'massFilters' => $filters,
        ]);       
    }

    /**
     * Displays a single ClientOrderMaster model.
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
     * Creates a new ClientOrderMaster model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreateClientOrder()
    {        
        $model = new ClientOrderMaster();

        if ($idOrder = Yii::$app->request->get('id_order') ?? NULL) { $model->id_order = $idOrder; }
        
        $model->created_at = date('U');
        $model->id_region = Yii::$app->session->get('id_region'); 
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }
       
        $clients = Yii::$app->db->createCommand('SELECT id_klient as id, username, imya, familiya, otchestvo '
                . ' FROM klient k LEFT JOIN user u ON k.id_klient=u.id WHERE id_region=' . $model->id_region)->queryAll();
        
        $orders = Zakaz::find()->select(['id', 'name', 'cena', 'id_vid_work', 'id_navik', 'reyting_start'])
                ->where(['id_status_zakaz' => VidStatusZakaz::ORDER_NEW, 'id_region' => $model->id_region])
                ->asArray()->all();
        
        if (empty($clients)) Yii::$app->session->setFlash ('message', 'Не найдено ниодного клиента');
        if (empty($orders)) Yii::$app->session->setFlash ('message', 'Заявок со статусом "новая" 0');
        
        return $this->render('create-client-order', [
            'model' => $model, 'clients' => $clients, 'orders' => $orders, 
            'filters' => ClientOrderMaster::getRelationTablesArray()
        ]);
    }  
    

    public function actionCreateOrderMaster($id)
    {           
        $model = $this->findModel($id);
            
        if ($model->id_master !== NULL) {
            Yii::$app->session->setFlash('message', 'Заявка уже занята мастером №' . $model->id_master);
            return $this->redirect('index');
        }
        
        if (!($model->load(Yii::$app->request->post()) && $model->validate())) {            
            return $this->render('create-order-master', ['model' => $model, 
                    'free' => $this->findMaster($model->id_order)
            ]);
        }  
            
        if (count($this->findMaster($model->id_order, $model->id_master))) {

            $transaction = Yii::$app->db->beginTransaction();               
            $change = VidChangeParametr::find()->limit(1)->one();
            $order = Zakaz::findOne(['id' => $model->id_order]);
            $master = Master::findOne(['id_master' => $model->id_master]);
            $master->scenario = Master::SCENARIO_UPDATE_HEAD_MANAGER;
            $order->scenario = Zakaz::SCENARIO_UPDATE_HEAD_MANAGER;
            
            $master->balans -= (int)($order->cena * $change->balans_delete / 100);
            $order->id_status_zakaz = VidStatusZakaz::ORDER_EXECUTES;
                    
            try {
                if ($model->save() && $master->save() && $order->save()) {
                    $transaction->commit();
                    Yii::$app->session->setFlash('message', 'Успех операции');
                } else { $transaction->rollBack(); }                 
                return $this->redirect('index');
            } catch(\Exception $e) { 
                $transaction->rollBack();
            } catch(\Throwable $e) {
                $transaction->rollBack();
            }
        } 
                    
        if (!count($free = $this->findMaster($model->id_order))) { 
                Yii::$app->session->setFlash('message', 'Требования заявки слишком высоки, '
                        . 'не найден ни один подходящий мастер');
            return $this->redirect('index');
        }            
        
        return $this->render('create-order-master', ['model' => $model, 'free' => $free,]);
    }
    
    
    protected function findMaster($id, $idMaster = '')
    {        
        $zakaz = Yii::$app->db->createCommand('SELECT z.id_vid_work, sort, '
                . 'id_region, cena, balans_delete, reyting_start FROM zakaz z, vid_navik n, '
                . 'vid_change_parametr p WHERE id_navik=n.id AND z.id=' 
                . $id)->queryOne();
        
        if ($idMaster != '' && is_int($idMaster)) { 
            $idMaster = ' AND m.id_master=' . $idMaster;
        } else { $idMaster = ''; }       
        $massMaster = Yii::$app->db->createCommand('SELECT m.id_master AS id, CONCAT(m.imya, " ", m.familiya, " ", m.otchestvo) AS fio, '
            . ' m.reyting, m.phone FROM master m,'
            . ' client_order_master z, master_work_navik mwn, vid_navik n WHERE mwn.id_vid_navik=n.id AND m.id_region=' 
            . $zakaz['id_region'] . ' AND m.reyting >=' . $zakaz['reyting_start'] 
            . ' AND m.balans >=' . (int)($zakaz['cena'] * $zakaz['balans_delete'] / 100) 
            . ' AND mwn.id_master=m.id_master AND mwn.id_vid_work=' . $zakaz['id_vid_work']
            . ' AND n.sort >=' . $zakaz['sort'] . ' ' . $idMaster . ' AND (m.id_master in (SELECT '
            . ' mas.id_master FROM master mas LEFT JOIN client_order_master zas ON mas.id_master=zas.id_master'
            . ' WHERE zas.id_master IS null AND mas.id_region='. $zakaz['id_region'] .') OR m.limit_zakaz > '
            . ' (SELECT COUNT(mz.id_master) FROM client_order_master mz, zakaz z WHERE mz.id_order=z.id '
            . ' AND mz.id_master=m.id_master AND z.id_status_zakaz NOT IN ('. VidStatusZakaz::ORDER_CANCELLED.', '. VidStatusZakaz::ORDER_EXECUTED .', '. VidStatusZakaz::ORDER_MASTER_INABILITY .'))) '
            . ' GROUP BY m.id_master')->queryAll();         
        
        /*$massMaster = Yii::$app->db->createCommand('SELECT m.id_master AS id, CONCAT(m.imya, " ", m.familiya, " ", m.otchestvo) AS fio, '
            . ' m.reyting, m.phone FROM master m,'
            . ' master_vs_zakaz z, master_work_navik mwn, vid_navik n WHERE mwn.id_vid_navik=n.id AND m.id_region=' 
            . $zakaz['id_region'] . ' AND m.reyting >=' . $zakaz['reyting_start'] 
            . ' AND m.balans >=' . (int)($zakaz['cena'] * $zakaz['balans_delete'] / 100) 
            . ' AND mwn.id_master=m.id_master AND mwn.id_vid_work=' . $zakaz['id_vid_work']
            . ' AND n.sort >=' . $zakaz['sort'] . ' ' . $idMaster . ' AND (m.id_master in (SELECT '
            . ' mas.id_master FROM master mas LEFT JOIN master_vs_zakaz zas ON mas.id_master=zas.id_master'
            . ' WHERE zas.id_master IS null AND mas.id_region='. $zakaz['id_region'] .') OR m.limit_zakaz > '
            . ' (SELECT COUNT(mz.id_master) FROM master_vs_zakaz mz WHERE mz.id_master=m.id_master)) '
            . ' GROUP BY m.id_master')->queryAll();    
        */    
        return $massMaster;
    }
    
    
    public function actionOrderEnd()
    {
        $id = Yii::$app->request->post('id');                
        $model = ClientOrderMaster::find()->joinWith('order')->where(['client_order_master.id' => $id])
                ->andWhere(['id_status_zakaz' => [ VidStatusZakaz::ORDER_EXECUTES, 
                        VidStatusZakaz::ORDER_REQUEST_EXECUTE, VidStatusZakaz::ORDER_REQUEST_REJECTION ]])
                ->limit(1)->one();  
        
        if ($model) {  
            $transact = Yii::$app->db->beginTransaction();   
            
            $master = Master::findOne(['id_master' => $model->id_master]);                     
            $order = Zakaz::findOne(['id' => $model->id_order]);
            $change = VidChangeParametr::findOne(['name' => 'default']);   
            $order->scenario = Zakaz::SCENARIO_UPDATE_HEAD_MANAGER;                                     
            $order->id_status_zakaz = VidStatusZakaz::ORDER_EXECUTED;
            
            try {  
                if ($master) {
                    $master->scenario = Master::SCENARIO_UPDATE_HEAD_MANAGER;
                    $master->reyting += $change->reyting_add;
                    $master->save();
                } else {
                    $order->id_status_zakaz = VidStatusZakaz::ORDER_CANCELLED;
                }
                
                if ($order->save()) {
                    $transact->commit(); 
                    Yii::$app->session->setFlash('message', 'Успех операции');
                } else { $transact->rollBack(); }   
            } catch (\Exception $e) {
                $transact->rollBack();
            } catch (\Throwable $e) {  $transact->rollBack(); }                 
        }   
        
        return $this->redirect('index');
    }
    
    
    
    

    /**
     * Updates an existing ClientOrderMaster model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    
    /**
     * Deletes an existing ClientOrderMaster model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete()
    {        
        if (!$id = Yii::$app->request->post('id')) return;
        
        $model = $this->findModel($id);
        
        if (Zakaz::findOne([$model->id_order])) {
            if (!$this->rollBackMasterParametrs($model)) return;
        }
        
        if (!$model->delete()) return;       

        return $this->redirect(['index']);
    }
    
    
    /**
     * Снимает с заявки мастера
     * @return type
     */
    public function actionDeleteMaster()
    {
        if (!$id = Yii::$app->request->post('id')) {            
            return $this->redirect('index');
        }
        $model = $this->findModel($id);
        
        if (!$this->rollBackMasterParametrs($model)) {
            return $this->redirect('index');
        }
        
        $model->id_master = NULL;
        if ($model->save()) {
            Yii::$app->session->setFlash('message', 'Успех операции');
            return $this->redirect(['index']);
        }
        
        return $this->redirect('index');
    }
    
    
    /**
     * Сбрасывает параметры мастера при снятии с заявки
     * @param type $model
     * @return boolean
     */
    protected function rollBackMasterParametrs($model)
    {
        $order = Zakaz::find()->where(['id' => $model->id_order])->limit(1)->one();
        if (!$order) { return FALSE; }
        
        $master = Master::find()->where(['id_master' => $model->id_master])->limit(1)->one(); 
        
        if ($master) {
            $master->scenario = Master::SCENARIO_UPDATE_HEAD_MANAGER;                
            $change = VidChangeParametr::find()->limit(1)->one();
            $master->balans = (int)($master->balans + ($order->cena * $change->balans_add) / 100);

            if ($order->id_status_zakaz == VidStatusZakaz::ORDER_REQUEST_REJECTION) {
                $master->reyting = (int)($master->reyting - ($master->reyting * $change->reyting_delete) / 100);
            }
            if (!$master->save()) { return FALSE; }
        }
        
        $order->scenario = Zakaz::SCENARIO_UPDATE_HEAD_MANAGER;
        $order->id_status_zakaz = VidStatusZakaz::ORDER_NEW;
        if ($order->save()) { return TRUE; }                
        return FALSE;
    }
      

    /**
     * Finds the ClientOrderMaster model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ClientOrderMaster the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ClientOrderMaster::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
 
}
