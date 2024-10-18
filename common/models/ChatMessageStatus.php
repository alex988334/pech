<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "chat_message_status".
 *
 * @property string $id_message № сообщения
 * @property string $id_user № пользователя
 * @property string $status_message Статус сообщения
 */
class ChatMessageStatus extends \yii\db\ActiveRecord
{
    /**
     * Константы статусов сообщений
     */    
  //  const MESSAGE_SAVE = 'save';
    const MESSAGE_SEND = 'send';
    const MESSAGE_DELIVERED = 'delivered';
    const MESSAGE_READED = 'readed'; 
   // const MESSAGE_BLACK_LIST = 'black_list';
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat_message_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_message', 'id_user', 'status_message', 'date', 'time'], 'required'],
            [['id_message', 'id_user'], 'integer'],
            [['status_message'], 'string', 'max' => 20],
            [['date', 'time'], 'safe'],
            [['id_message', 'id_user'], 'unique', 'targetAttribute' => ['id_message', 'id_user']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_message' => '№ сообщения',
            'id_user' => '№ пользователя',
            'status_message' => 'Статус сообщения',
            'date' => 'Дата',
            'time' => 'Время',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'id_user']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessage()
    {
        return $this->hasOne(ChatMessage::className(), ['id' => 'id_message']);
    }
}
