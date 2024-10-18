<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
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
use common\models\ManagerTableGrant;
use common\models\HistoryEntry;


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
                        'actions' => ['user', 'reset', 'block-user'],
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
        Yii::debug(['params' => Yii::$app->request->queryParams]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $fields = ManagerTableGrant::findBySql('SELECT t.name, t.alias, `id_table_field`, '
                . '`field_width`, `visibility_field` FROM `manager_table_grant` tg, '
                . ' manager_table t WHERE tg.id_table_field=t.id AND t.parent IN '
                . ' (SELECT id FROM manager_table WHERE name IN ("user"))'
                . ' AND id_manager=' . Yii::$app->user->getId())->asArray()->all();         
        
        $massFilters = User::getRelationTablesArray();
        $massFilters['vidStatus'] = [ User::STATUS_ACTIVE => 'Активен', 
                User::STATUS_BLOCKED => 'Заблокирован', User::STATUS_DELETED => 'Удален' ];
        
        return $this->render('index', [                
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,   
            'fields' => $fields,
            'massFilters' => $massFilters,
        ]);
    }
    
    public function actionReset()
    {
        $region = Yii::$app->session->get('id_region'); 
        
       // if ($id == '0') return $this->redirect(['/site/user', 'page' => Yii::$app->session->get('page') ?? 1]);
        
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
                $flag = false;
                Yii::$app->session->setFlash('message', 'Этот пользователь не из вашего региона');
            }          
        } else { $flag = false;}     
        
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

    
    public function actionBlockUser()
    {
        if (($id = Yii::$app->request->post('id')) === NULL) {
            return $this->redirect('user');
     //       throw new InvalidParameterException('Params error');
        }
        
        $user = User::find()->where(['id' => $id])->with('role')->one();
        $role = current(Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId()));
        
        $flag = false;
        if ($role->name == AuthItem::HEAD_MANAGER 
                && ($user->role->item_name == User::KLIENT 
                || $user->role->item_name == User::MASTER 
                || $user->role->item_name == User::MANAGER)) $flag = true;
        if ($role->name == AuthItem::MANAGER 
                && ($user->role->item_name == User::KLIENT 
                || $user->role->item_name == User::MASTER)) 
                $flag = true;
                
        if ($flag && $user->username != 'system' && $user->role->item_name != User::ADMIN) {
            if ($user->status == User::STATUS_ACTIVE) {
                $user->status = User::STATUS_BLOCKED;
            } elseif ($user->status == User::STATUS_BLOCKED) {
                $user->status = User::STATUS_ACTIVE;
            }
            if ($user->save()) {
                Yii::$app->session->setFlash('message', 'Успех операции');
            }
        }
        
        return $this->redirect('user');
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
              //      Yii::$app->session->setFlash('message', 'Старое время: ' . $user['last_time'] . ' Новое время: ' . time());
                    Yii::$app->db->createCommand('DELETE FROM session WHERE user_id=' . $user['user_id'])->execute();                    
                }  
            }     
        }        
        
        if ($flag && $model->login()) {
            
            $id = Yii::$app->user->id;
            $role = current(Yii::$app->authManager->getRolesByUser($id))->name;              
            $session->set('role', $role); 
            
            $entry = new HistoryEntry();
            $entry->id_user = $id;
            $entry->date = date('Y-m-d');
            $entry->time = date('H:i:s');
            $entry->ip = Yii::$app->request->userIP;
            $entry->action = HistoryEntry::USER_ENTRY;
            $entry->save();
            
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
        $entry = new HistoryEntry();
            $entry->id_user = Yii::$app->user->id;
            $entry->date = date('Y-m-d');
            $entry->time = date('H:i:s');
            $entry->ip = Yii::$app->request->userIP;
            $entry->action = HistoryEntry::USER_EXITED;
            $entry->save();
        
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
