<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\VidWork;
use common\models\VidWorkSearch;

/**
 * Контроллер видов работ
 * @author Gradinas
 */
class WorkController extends Controller {
    
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
    
        //  выводит список видов работ
    public function actionIndex()
    {        
        $searchModel = new VidWorkSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return $this->render('index', [                
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,            
        ]);
    }
    
        //  обновляет параметры вида работ
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect('index');
        } 
                
        return $this->render('update', ['model' => $model]);
    }
    
        //  создает новый вид работ
    public function actionCreate()
    {
        $model = new VidWork();
        
        if ($model->load(Yii::$app->request->post()) && $model->save()){
            return $this->redirect('index');
        }
        
        return $this->render('create', ['model' => $model]);
    }
    
        //  удаляет вид работ
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
        return VidWork::findOne(['id' => $id]);
    }
    
    
}
