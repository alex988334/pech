<?php
namespace common\models;

use Yii;
use yii\base\Model;
use common\models\Session;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;
    
    public function attributeLabels() {
        return [
            'username' => 'Логин',
            'password' => 'Пароль',
            'rememberMe' => 'Запомнить меня',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        Yii::debug('ОТРАБОТАЛ ВНЕШНИЙ VALIDATEPASSWORD В LOGINFORM');
        
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            
            /*if (!$user || !Session::findOne(['user_id' => $user->id])) {
             //   $this->addError($attribute, 'Вы уже зашли');
                return;
            } else*/
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            } 
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        Yii::debug('ОТРАБОТАЛ ВНЕШНИЙ LOGIN В LOGINFORM');
        if ($this->validate()) {
            Yii::debug('ПРОЙДЕНА ВАЛИДАЦИЯ');
            return Yii::$app->user->login($this->getUser(), /**/$this->rememberMe ?  0/*3600 * 24 * 30*/ : 0);
        }
        
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
