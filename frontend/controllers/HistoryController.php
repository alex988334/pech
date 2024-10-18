<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\controllers;


use Yii;
use yii\base\InvalidParamException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use common\models\User;
use common\models\HistoryEntry;
use common\models\HistoryEntrySearch;

/**
 * Description of History
 *
 * @author Gradinas
 */
class HistoryController extends Controller {
    
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
                        'actions' => ['index'],
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
    
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    
    public function actionIndex()
    {
        $searchModel = new HistoryEntrySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (Yii::$app->request->get('page') != null) { 
            Yii::$app->session->set('page', Yii::$app->request->get('page'));         
        }
       /* $fields = ManagerTableGrant::findBySql('SELECT t.name, t.alias, `id_table_field`, '
                . '`field_width`, `visibility_field` FROM `manager_table_grant` tg, '
                . ' manager_table t WHERE tg.id_table_field=t.id AND t.parent IN '
                . ' (SELECT id FROM manager_table WHERE name IN ("'. MasterWorkNavik::tableName() .'"))'
                . ' AND id_manager=' . Yii::$app->user->getId())->asArray()->all();
    */    
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        //    'fields' => $fields,
            'massFilters' => HistoryEntry::getRelationTablesArray()
        ]);
    }
}
