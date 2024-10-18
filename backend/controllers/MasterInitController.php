<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\VidInitializationMaster;

/**
 * Контроллер начальных настроек мастера
 * @author Gradinas
 */
class MasterInitController extends Controller {
    
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
                        'actions' => ['index', 'update'],
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
    
        //  отображает список начальных параметров мастера
    public function actionIndex()
    {        
        $model = VidInitializationMaster::find()->one();
        
        return $this->render('index', ['model' => $model]);
    }
    
        //  обновляет список начальных параметров мастера
    public function actionUpdate()
    {
        $model = VidInitializationMaster::find()->one();
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect('index');
        } 
                
        return $this->render('update', ['model' => $model]);
    }  
  
}
