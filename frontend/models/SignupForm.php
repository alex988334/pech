<?php
namespace frontend\models;

use yii\base\Model;
use common\models\User;
use common\models\Klient;
use common\models\Master;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $phone;
    public $password;
    public $password1;
    public $email;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'Это имя пользователя уже занято'],
            ['username', 'string', 'min' => 3, 'max' => 255],

          /*  ['phone', 'trim'],
            ['phone', 'required'],
        */    ['email', 'email'],
            
       /*     ['phone', 'string', 'max' => 11],
            ['phone', 'unique', 'targetClass' => '\common\models\User', 'message' => 'Такой почтовый ящик уже зарегистрирован'],
*/
            [['password', 'password1'], 'required'],
            [['password', 'password1'], 'string', 'min' => 6],
        ];
    }

    public function attributeLabels() {
        return [
            'username' => 'Имя пользователя (логин)',
            'email' => 'Почта',
            'password' => 'Пароль',
            'password1' => 'Подтверждение пароля'
        ];
    }


    /**
     * Регистрация нового пользователя
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        if ($this->password != $this->password1){
            return null;
        }
        
        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        
        return $user->save() ? $user : null;
    }
}
