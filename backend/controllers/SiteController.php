<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\SignupForm;
use yii\web\User;
use common\models\AuthAssignment;
use common\models\AuthItem;

/**
 * Site controller
 */
class SiteController extends Controller
{
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
                        'actions' => ['login'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['error'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                /*    [ 
                        'actions' => ['index', 'signup'],
                        'allow' => true, 
                        'roles' => ['manager'], 
                    ], 
                    //  */
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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
    
    /**
     * Авторизация
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();                                             //  если уже авторизован, то на домашнюю страницу
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();                                             //  если авторизация успешна - переход на предыдущую страницу
        } else {                                                                //  иначе отображаем ошибки
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Разлогинивание
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
    
        //  Регистрация нового администратора
    public function actionSignup(){
        
        $model = new SignupForm();
            //  сохраняем нового пользователя
        if ($model->load(Yii::$app->request->post()) && $user = $model->signup()) {    
            $model1 = new AuthAssignment();                                     //  создаем модель роли администратора
            $model1->user_id = (string)$user->id;
            $model1->item_name = AuthItem::ADMIN;
            $model1->created_at = date('U');
            if ($model1->save()) {                                              //  сохраняем
                return $this->render('index');
            } else {
                debugArray($model1->errors);                                    //  печатаем ошибки
               // Yii::$app->session->setFlash('message', $model1->getErr);
            }           
        } 
        
        $model->password = '';                                                  //  сбросим строки паролей
        $model->password1 = '';
        return $this->render('signup', ['model' => $model]);
    }
}
