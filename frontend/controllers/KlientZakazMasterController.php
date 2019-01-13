<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * Description of Klient_Zakaz_Master
 *
 * @author Gradinas
 */
class KlientZakazMasterController extends Controller
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
                        'allow' => true,
                        'roles' => ['manager', 'head_manager'],
                    ],
                    [  
                        'actions' => ['update'],
                        'allow' => false,
                    ]
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
    
    public function actionIndex()
    {
        $searchModel = new KlientVsZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'massFilters' => $this->getRelationTablesArray()
        ]);
    }

    public function actionCreate()
    {
        
    }
    
    public function actionRebootRequestReject()
    {
        
    }
    
    public function actionAcceptReject()
    {
        
    }
    
    public function actionOrderExecuted()
    {
        
    }
    
    protected function getRelationTablesArray()
    {
        $vid = [];
        
        $vid['vidWork'] = VidWork::find()->select(['id', 'name'])->asArray()->all();
        
        
        
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->asArray()->all();
        
        return $vid;
    }
}
