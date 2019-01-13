<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "chat".
 *
 * @property string $id №
 * @property string $id_chat № чата
 * @property string $id_user № пользователя
 * @property string $parent_id № родительского сообщения (если это сообщение является ответом на вопрос)
 * @property string $message Сообщение
 * @property string $date Дата
 * @property string $time Время
 */
class OLDChat extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_chat', 'id_user', 'message', 'date', 'time'], 'required'],
            [['id_chat', 'id_user', 'parent_id'], 'integer'],
            [['message'], 'string'],
            [['date', 'time'], 'safe'],
            [['login'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№',
            'id_chat' => '№ чата',
            'id_user' => '№ пользователя',
            'parent_id' => '№ родительского сообщения (если это сообщение является ответом на вопрос)',
            'message' => 'Сообщение',
            'date' => 'Дата',
            'time' => 'Время',
        ];
    }
    
    public function getUsers()
    {
        return $this->hasOne(User::className(), ['id' => 'id_user']);
    }
    
    
}
