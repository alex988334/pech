<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\VidShag;
use common\models\VidShagSearch;

/**
 * Контроллер шагов выполнения заявок
 * @author Gradinas
 */
class StepsController extends Controller {
    
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
                        'roles' => ['admin'],
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
    
        //  таблица шагов выполнения
    public function actionIndex()
    {        
        $searchModel = new VidShagSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return $this->render('index', [                
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,            
        ]);
    }
        
        //  обновление шага выполнения
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect('index');
        } 
                
        return $this->render('update', ['model' => $model]);
    }
    
        //  создание шага выполнения заявки
    public function actionCreate()
    {
        $model = new VidShag();
        
        if ($model->load(Yii::$app->request->post()) && $model->save()){
            return $this->redirect('index');
        }
        
        return $this->render('create', ['model' => $model]);
    }
    
        //  удаление шага выполнения
    public function actionDelete()
    {        
        $model = $this->findModel(Yii::$app->request->post('id'));
        if ($model != null && $model->delete()) {
            Yii::$app->session->setFlash('message', 'Удаление успешно');
        } else {
            Yii::$app->session->setFlash('message', 'Объект удаления не найден');
        }
        
        return $this->redirect('index');
    }
    
    protected function findModel($id)
    {
        return VidShag::findOne(['id' => $id]);
    }
    
    
}
