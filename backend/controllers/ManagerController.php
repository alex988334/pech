<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use backend\models\ManagerSearch;
use backend\models\Manager;
use common\models\User;
use common\models\AuthAssignment;
use common\models\AuthItem;

/**
 * Description of RegionController
 *
 * @author Gradinas
 */
class ManagerController extends Controller {
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
            //    'only' => ['login'],
                'rules' => [ 
                    [
                        'actions' => ['error'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],                    
                    [
                        'actions' => ['index', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['admin'],                                   //  действия разрешены только для администратора
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
        ];
    }
    
        //  возращает представление таблицы менеджеров
    public function actionIndex()
    {        
        $searchModel = new ManagerSearch();                                     //  создаем поисковую модель
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);  //  создаем провайдера данных   
        
        return $this->render('index', [                
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'massFilters' => Manager::getRelationTablesArray(),                 //  добавляем таблицы допустимых значений
        ]);
    }
    
        //  обновляет данные менеджера
    public function actionUpdate($id)
    {
        $manager = Manager::findOne(['id_manager' => $id]);                     //  ищем по идентификатору в таблицах:         
        $user = User::findOne(['id' => $id]);                                   //  менеджеры, пользователи, роли
        $role = AuthAssignment::findOne(['user_id' => $id]);

        if ($manager->load(Yii::$app->request->post()) && $manager->save()) {   //  загружаем в найденные модели данные и сохраняем             
            $user->load(Yii::$app->request->post());
            $user->id = $manager->id_manager;
            if ($user->password_hash != $user->oldAttributes['password_hash']) {
                $user->setPassword($user->password_hash);
            }
            $user->save();

            $role->load(Yii::$app->request->post());
            $role->user_id = $manager->id_manager;
            $role->save();

            return $this->redirect('index');                                    //  перенаправляем на страницу таблицы менеджеров
        }     
            //  иначе отображаем ошибки на странице обновления    
        return $this->render('update', ['manager' => $manager, 'user' => $user, 
            'role' => $role, 'massFilters' => Manager::getRelationTablesArray()
        ]);                                                     
    }
    
        //  создание нового менеджера
    public function actionCreate()
    {
        $manager = new Manager();                                               //  создаем новые модели таблиц:
        $user = new User();                                                     //  менеджеры, пользователи, роли
        $role = new AuthAssignment();

        if ($user->load(Yii::$app->request->post()) && $user->validate()) {     //  загружаем в модель пользователя данные и проверяем             
            
            if ($user->password_hash == NULL) {                                 //  если пароль не задан используем значение имени пользователя 
                $user->password_hash = $user->username;
            }            
            $user->setPassword($user->password_hash);                           //  шифруем значение пароля
            if ($user->save()) {                                                //  сохраняем пользователя
                $manager->id_manager = $user->id;                               //  в модель менеджера загружаем данные и сохраняем
                if ($manager->load(Yii::$app->request->post()) && $manager->save()){                    
                    $role->user_id = $user->id;                                 //  в модель ролей загружаем данные и сохраняем
                    if ($role->load(Yii::$app->request->post()) && $role->save()) {
                        return $this->redirect('index');                        //  перенаправляем на страницу таблицы менеджеров
                    }
                }
            }          
        }     
        $filters = Manager::getRelationTablesArray();                           //  получаем массив таблиц допустимых значений
        $filters['vidRole'] = [                                                 //  в массиве заменяем список допустимых ролей
            AuthItem::HEAD_MANAGER => 'Руководитель региона', 
            AuthItem::MANAGER => 'Менеджер'
        ];
        
        return $this->render('create', ['manager' => $manager, 'user' => $user, 
            'role' => $role, 'massFilters' => $filters
        ]);
    }
    
        //  удаление менеджера
    public function actionDelete()
    {        
        $model = $this->findModel(Yii::$app->request->post('id'));              //  ищем модель по идентификатору менеджера
        if ($model != NULL) {                                                   //  проверяем что найдена
            $user = User::findOne(['id' => $model->id_manager]);                //  ищем модель пользователя и если найдена удаляем ее
            if ($user != NULL) $user->delete();
            $role = AuthAssignment::findOne(['user_id' => $model->id_manager]); //  ищем модель роли пользователя и если найдена удаляем
            if ($role != NULL) $role->delete();
            if ($model->delete()){                                              //  удаляем модель менеджера
                Yii::$app->session->setFlash('message', 'Удаление успешно');
                return $this->redirect('index');                                //  перенаправляем на страницу менеджеров
            }
        }
        
        Yii::$app->session->setFlash('message', 'Объект удаления не найден');        
        return $this->redirect('index');
    }
    
    protected function findModel($id)
    {
        return Manager::findOne(['id' => $id]);
    }
    
    
}
