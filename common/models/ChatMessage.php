<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "chat_message".
 *
 * @property string $id №
 * @property string $id_chat № чата
 * @property string $id_user № пользователя
 * @property string $parent_id № родительского сообщения (если это сообщение является ответом на вопрос)
 * @property string $message Сообщение
 * @property string $date Дата
 * @property string $time Время
 */
class ChatMessage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat_message';
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
            [['file'], 'string', 'max' => 250],
            [['date', 'time'], 'safe'],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['id_user' => 'id']],
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
            'id_user' => '№ автора сообщения',
            'parent_id' => '№ родительского сообщения (если это сообщение является ответом на вопрос)',
            'message' => 'Сообщение',
            'file' => 'Название файлов прикрепленных к сообщению',
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
    public function getStatusMessage()
    {
        return $this->hasOne(ChatMessageStatus::className(), ['id_message' => 'id', 'id_user' => 'id_user']);
    }
    
    public function toString()
    {
        $str = '';
        foreach ($this as $key => $value) {
            if (is_string($value) && $value != null) $value = '"' . $value . '"';
            $str = $str . '"' . $key . '":' . $value . ', ';
        }
        return '{' . substr($str, 0, strlen($str) - 2) . '}';
    }
}
