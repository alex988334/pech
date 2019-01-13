<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use common\models\Manager;
use common\models\Master;
use common\models\Klient;
use common\models\User;
use common\models\Session;
use common\models\AuthItem;
use frontend\models\UpdateForm;
use common\models\UserSearch;



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
                'rules' => [ 
                    [ 
                        'actions' => ['login', 'error'], 
                        'allow' => true,                         
                    ], 
                    [ 
                        'actions' => ['login'],
                        'allow' => true, 
                        'roles' => ['?'], 
                    ],
                    [ 
                        'actions' => ['logout'],
                        'allow' => true, 
                        'roles' => ['@'], 
                    ], 
                    [ 
                        'actions' => ['change-login-password'],
                        'allow' => true, 
                        'roles' => ['master', 'klient', 'head_manager'], 
                    ],
                    [
                        'actions' => ['user', 'reset'],
                        'allow' => true, 
                        'roles' => ['manager', 'head_manager'], 
                    ],
                    [ 
                        'actions' => ['signup'],
                        'allow' => false, 
                 //       'roles' => ['head_manager'], 
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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    
    public function actionUser()
    {
        if (Yii::$app->request->get('page') != null) { 
            Yii::$app->session->set('page', Yii::$app->request->get('page'));         
        }
        
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [                
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,         
        ]);
    }
    
    public function actionReset()
    {
        $region = Yii::$app->session->get('id_region'); 
        
        if (($id = Yii::$app->request->post('id')) && (((string)((int)$id))) == $id) { 
            
            $master = Master::find()->select('id_region')->where(['id_master' => $id])->limit(1)->scalar();
            $manager = Manager::find()->select('id_region')->where(['id_manager' => $id])->limit(1)->scalar();
            $klient = Klient::find()->select('id_region')->where(['id_klient' => $id])->limit(1)->scalar();
            $flag = false;
            
            if (!$master && !$manager && !$klient) {
                $flag = true;
            } elseif ($master == $region || $manager == $region || $klient == $region) {
                $flag = true;
            } else {
                Yii::$app->session->setFlash('message', 'Этот пользователь не из вашего региона');
            }          
        }       
        
        if ($flag && $type = Yii::$app->request->post('type')) {
            switch ($type) {
                case 2: $query = 'UPDATE user SET password_hash="'
                    . password_hash('PechnoyMir', PASSWORD_DEFAULT) .'" WHERE id=' . $id ;
                    break;
                case 3: $query = 'UPDATE user SET imei=NULL WHERE id=' . $id ;
                    break;
            }
            if (Yii::$app->db->createCommand($query)->execute()) {
                Yii::$app->session->setFlash('message', 'Успех');
            }
        }        
        return $this->redirect(['/site/user', 'page' => Yii::$app->session->get('page') ?? 1]);
    }

    public function actionIndex(){ 
        
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        $session = Yii::$app->session;
        $model = new LoginForm(); 
        $flag = true;
        if (!$model->load(Yii::$app->request->post())) { 
            $flag = false;             
        } 
        if ($model->validate()) {
            
            $user = Yii::$app->db->createCommand('SELECT s.user_id, s.last_time FROM session s, user u WHERE u.id=s.user_id AND u.username=:username LIMIT 1')
                    ->bindParam(':username', $model->username)->queryOne();  
  
            if ($user) {
                if (($user['last_time'] + 1200) > time()) {   
                    $flag = false;  
                    $session->setFlash('message', 'Вы уже зашли! Старое время: ' . $user['last_time'] . ' Новое время: ' . time());
                } else {
                    Yii::$app->session->setFlash('message', 'Старое время: ' . $user['last_time'] . ' Новое время: ' . time());
                    Yii::$app->db->createCommand('DELETE FROM session WHERE user_id=' . $user['user_id'])->execute();                    
                }  
            }     
        }        
        
        if ($flag && $model->login()) {
            
            $id = Yii::$app->user->id;
            $role = current(Yii::$app->authManager->getRolesByUser($id))->name;              
            $session->set('role', $role); 

            switch ($role) {
                case AuthItem::MASTER : 
                    $id = Master::find()->select('id_region')->where(['id_master' => $id])
                        ->limit(1)->scalar();
                    $homeUrl = '/master/kabinet';
                break;
                case AuthItem::MANAGER :                    
                case AuthItem::HEAD_MANAGER :
                    $id = Manager::find()->select('id_region')->where(['id_manager' => $id])
                        ->limit(1)->scalar();
                    $homeUrl = '/manager/index';                        
                break;
            }

            $session->set('id_region', $id);
            Yii::$app->homeUrl = $homeUrl;

            return $this->redirect($homeUrl);
        }  
        
        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);

    }
    
    public function actionLogout()
    {
        Yii::$app->user->logout();
     //   return $this->refresh();
        return $this->goHome();
    }
    
    
    public function actionChangeLoginPassword()
    {
        $model = new UpdateForm();
        
        $role = Yii::$app->session->get('role');
        
        if ($role == AuthItem::HEAD_MANAGER) {
            $mass = Manager::find()->select(['id_manager'])->with('user')->asArray()->all();
        } else { $mass = []; }
        
        if ($model->load(Yii::$app->request->post())) {            
            
            if ($model->update()) {
                
                switch ($role) {
                    case AuthItem::MASTER :     
                        return $this->redirect('/master/kabinet');                        
                        break;
                    case AuthItem::MANAGER :                    
                    case AuthItem::HEAD_MANAGER : 
                        return $this->redirect('/manager/index');                                               
                        break;
                    case AuthItem::KLIENT :
                        return $this->redirect('/klient/kabinet');     
                        break;
                }                
            }
        }        
        
        return $this->render('change-login-password', ['model' => $model, 'mass' => $mass]);
    }    
}
