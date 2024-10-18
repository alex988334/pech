<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\VidRegion;
use common\models\VidRegionSearch;

/**
 * Контроллер регионов
 * @author Gradinas
 */
class RegionController extends Controller {
    
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
                        'roles' => ['@'],
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
    
        //  отображает таблицу регионов
    public function actionIndex()
    {        
        $searchModel = new VidRegionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return $this->render('index', [                
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,            
        ]);
    }
    
        //  обновление региона
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect('index');
        } 
                
        return $this->render('update', ['model' => $model, 
                'massFilters' => VidRegion::getRelationTablesArray()]);
    }
    
        //  создание нового региона
    public function actionCreate()
    {
        $model = new VidRegion();
        if ($model->dolgota == NULL || $model->shirota == NULL){                //  если долгота или широта нового региона не заданы
            $model->dolgota = 99.505405;
            $model->shirota = 61.698653;
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->save()){
            return $this->redirect('index');
        }
        
        return $this->render('create', ['model' => $model, 
                'massFilters' => VidRegion::getRelationTablesArray()]);
    }
    
        //  удаление региона
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
        return VidRegion::findOne(['id' => $id]);
    }
    
    
}
