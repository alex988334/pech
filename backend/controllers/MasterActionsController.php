<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\VidChangeParametr;

/**
 * Контроллер действий мастеров
 * @author Gradinas
 */
class MasterActionsController extends Controller {
    
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
    
        //  отображает список действий
    public function actionIndex()
    {        
        $model = VidChangeParametr::find()->one();
        
        return $this->render('index', ['model' => $model]);
    }
    
        //  обновляет параметры действия мастера
    public function actionUpdate()
    {
        $model = VidChangeParametr::find()->one();
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect('index');
        } 
                
        return $this->render('update', ['model' => $model]);
    }  
  
}
