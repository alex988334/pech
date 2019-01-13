<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;
use common\models\Klient;
use common\models\Master;
use common\models\AuthItem;

/**
 * Signup form
 */
class UpdateForm extends Model
{
    const ONE_MONTH = 2592000;
    
    public $username;
    public $password;
    public $password1;
    public $id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['username', 'password'], 'trim'],
            [['username', 'password'], 'string', 'min' => 6, 'max' => 30]
        ];
    }

    public function attributeLabels() {
        return [
            'id' => '№ менеджера',
            'username' => 'Новое имя пользователя',
            'password' => 'Новый пароль',
            'password1' => 'Подтверждение пароля'
        ];
    }


    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function update()
    {
        if (!$this->validate()) {
            return null;
        }
        $bool = false;
        $role = Yii::$app->session->get('role');
        
        if ($role == AuthItem::HEAD_MANAGER) {
                $id = $this->id;
        } else {
            $id = Yii::$app->user->getId();
        }        
        
        $model1 = User::find()->where(['id' => $id])->limit(1)->one();
        if ($this->username && (
                    ($role == User::MASTER && ($model1->updated_at + self::ONE_MONTH) < time()) 
                    || $role == User::KLIENT || $role == User::HEAD_MANAGER)
                ) {
                
            $sc = Yii::$app->db->createCommand('SELECT id FROM user WHERE username=:name AND id <>' 
                    . $id . ' LIMIT 1')->bindValue(':name', $this->username)->queryScalar();
            if (!$sc) {
                $bool = Yii::$app->db->createCommand('UPDATE `user` SET `username`=:name, '
                        . ' `updated_at`=' . time() . ' WHERE `id`=' . $id)
                        ->bindValues([':name' => $this->username])->execute();
                Yii::$app->session->setFlash('message', 'Логин изменен');
            } else {
                Yii::$app->session->setFlash('message', 'Такой логин уже зарегистрирован');
            }            
        }
        
        if ($this->password) {
            $user = new User();
            $user->setPassword($this->password);
            
            $bool = Yii::$app->db->createCommand('UPDATE `user` SET `password_hash`=:pass '
                        . ' WHERE `id`=' . $id)
                        ->bindValues([':pass' => $user->password_hash])->execute();
            if (!$bool) {            
                Yii::$app->session->setFlash('message', 'Ошибка изменения пароля');
            }
        }

        return $bool;
    }
}
