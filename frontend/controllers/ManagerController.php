<?php

namespace frontend\controllers;

use Yii;
use common\models\Manager;
use common\models\ManagerSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\User;
use common\models\VidStatusZakaz;
use common\models\ManagerTableGrant;

/**
 * ManagerController implements the CRUD actions for Manager model.
 */
class ManagerController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['error'],
                        'allow' => true,                        
                    ],
                    [
                        'actions' => ['index', 'update', 'view', 'save-fields'],
                        'allow' => true,
                        'roles' => ['manager', 'head_manager']
                    ]
                ]
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
     * Lists all Manager models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->request->get('page') != null) { 
            Yii::$app->session->set('page', Yii::$app->request->get('page'));         
        }
        
        $searchModel = new ManagerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);        
        
        if (Yii::$app->session->get('role') == User::HEAD_MANAGER) { 
            $region = '';             
        } else {
            $region = 'AND id_region=' . Yii::$app->session->get('id_region');
        }        
        
        $massZakaz = Yii::$app->db->createCommand('SELECT z.id, w.name AS work_name, z.`name`, r.name AS region_name '
                . ' FROM (SELECT z.id FROM zakaz z LEFT JOIN klient_vs_zakaz kz ON z.id=kz.id_zakaz '
                . ' WHERE kz.id_zakaz IS NULL ' . $region . ') zak, zakaz z, vid_work w, vid_region r '
                . ' WHERE z.id_vid_work=w.id AND z.id=zak.id AND z.id_region=r.id ORDER BY `z`.`id` ASC')
                ->queryAll();
        
        $massKlientZakaz = Yii::$app->db->createCommand('SELECT * FROM (SELECT kz.id as zakaz FROM klient_vs_zakaz kz '
                . ' LEFT JOIN zakaz z ON kz.id_zakaz=z.id WHERE z.id IS null) as zakaz UNION (SELECT kz.id as klient'
                . ' FROM klient_vs_zakaz kz LEFT JOIN klient k ON kz.id_klient=k.id_klient WHERE k.id_klient IS null)')
                ->queryAll();
        
        $massKlient = Yii::$app->db->createCommand('SELECT k.id_klient, imya, familiya, otchestvo, r.name AS region_name'
                . ' FROM (SELECT k.id_klient FROM klient k LEFT JOIN klient_vs_zakaz kz ON k.id_klient=kz.id_klient '
                . ' WHERE kz.id_klient IS NULL ' . $region . ') kl, klient k, vid_region r '
                . ' WHERE k.id_klient=kl.id_klient AND k.id_region=r.id ORDER BY k.id_klient ASC')
                ->queryAll();
        
        $massZakazMaster = Yii::$app->db->createCommand('SELECT * FROM (SELECT mz.id AS zakaz FROM master_vs_zakaz mz '
                . 'LEFT JOIN zakaz z ON mz.id_zakaz=z.id WHERE z.id IS null) as zakaz UNION '
                . '(SELECT mz.id AS master FROM master_vs_zakaz mz LEFT JOIN master m ON mz.id_master=m.id_master '
                . 'WHERE m.id_master IS null)')
                ->queryAll();
        
        $massStatusZakaz = Yii::$app->db->createCommand('SELECT z.id FROM zakaz z JOIN master_vs_zakaz mz '
                . ' ON z.id=mz.id_zakaz WHERE (id_status_zakaz <> ' . VidStatusZakaz::ORDER_EXECUTES 
                . ' OR id_status_zakaz <> '. VidStatusZakaz::ORDER_REQUEST_REJECTION .') ' . $region)
                ->queryAll();
        
        $massUser = Yii::$app->db->createCommand('SELECT id, username FROM user WHERE id IN '
                . ' (SELECT res.id FROM (SELECT r.id, k.id_klient FROM (SELECT u.`id`, m.id_master FROM `user` u '
                . ' LEFT JOIN master m ON u.id=m.id_master WHERE m.id_master IS NULL) r LEFT JOIN klient k '
                . ' ON r.id=k.id_klient WHERE k.id_klient IS NULL) res LEFT JOIN manager m ON res.id=m.id_manager '
                . ' WHERE m.id_manager IS NULL)')
                ->queryAll();
        
        $mass = [
            [ 
                'lables' => ['№ пользователя', 'Логин'],
                'values' => $massUser,
                'error' => 'Нарушена целостность базы данных!!! У следующих пользователей была прервана регистрация (не мастер, не клиент и не менеджер)',
            ],
            [ 
                'lables' => ['№ заявки', 'Вид работ', 'Название', 'Регион'],
                'values' => $massZakaz,
                'error' => 'Нарушена целостность базы данных!!! У следующих заявок отсутствует связь с клиентами',
            ],
            [
                'lables' => ['№'],
                'values' => $massKlientZakaz,
                'error' => 'Нарушена целостность базы данных!!! Следующие записи в таблице "Клиенты и заявки" ссылаются '
                            . 'на несуществующие записи в таблицах "Клиенты" или "Заявки". '
                            . '(Это сообщение отображается у всех менеджеров во всех регионах)'
            ],
            [
                'lables' => ['№'],
                'values' => $massZakazMaster,
                'error' => 'Нарушена целостность базы данных!!! Следующие записи в таблице "Мастера и заявки" ссылаются '
                            . 'на несуществующие записи в таблицах "Мастера" или "Заявки" '
                            . '(Это сообщение отображается у всех менеджеров во всех регионах)'
            ],            
            [
                'lables' => ['№ клиента', 'Имя', 'Фамилия', 'Отчество', 'Регион'],
                'values' => $massKlient,
                'error' => 'Возможно нарушена целостность базы данных! У следующих клиентов нет заявок'
            ],
            [
                'lables' => ['№ заявки'],
                'values' => $massStatusZakaz,
                'error' => 'Нарушение статусов прикрепленных к мастерам заявок!!! '
                            . 'Статусы должны быть либо "выполняется", либо "запрос отказа"'
            ],
        ];
        
        $fields = ManagerTableGrant::findBySql('SELECT t.name, t.alias, `id_table_field`, '
                . '`field_width`, `visibility_field` FROM `manager_table_grant` tg, '
                . ' manager_table t WHERE tg.id_table_field=t.id AND t.parent IN '
                . ' (SELECT id FROM manager_table WHERE name IN ("'. Manager::tableName() .'"))'
                . ' AND id_manager=' . Yii::$app->user->getId())->asArray()->all(); 

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'fields' => $fields,
            'mass' => $mass,            
        ]);
    }

    /**
     * Displays a single Manager model.
     * @param integer $id
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
     * Creates a new Manager model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Manager();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Manager model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        $role = Yii::$app->session->get('role');
        if ($role == User::HEAD_MANAGER) {
            $model->scenario = Manager::SCENARIO_UPDATE_HEAD_MANAGER;
        } else {
            $model->scenario = Manager::SCENARIO_UPDATE_MANAGER;                 //  $role == User::MANAGER
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Manager model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Manager model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Manager the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Manager::find()->where(['id' => $id])->with('user')->limit(1)->one();
                
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    public function actionSaveFields()
    {
        if (!Yii::$app->request->isAjax) {
            return json_encode(['status' => 0, 'message' => 'Это не ajax']);
        }
        
        if ($mass = Yii::$app->request->post('mass')) {
            $id_manager = Yii::$app->user->getId();              
            foreach ($mass as $one){
                $query = 'UPDATE manager_table_grant SET visibility_field='
                        . $one['visible'] .' WHERE id_table_field='. $one['id'] 
                        .' AND id_manager='. $id_manager;
                $request = Yii::$app->db->createCommand($query);
                if (!$request->execute()) {
                    return json_encode(['status' => 0, 'message' => 'Некоторые поля не удалось сохранить: ' . $query]);
                }
            }
            return json_encode(['status' => 1, 'message' => 'Успех сохранения']);
        }
        return json_encode(['status' => 0, 'message' => 'Нечего сохранять']);
    }    
}
