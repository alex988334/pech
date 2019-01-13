<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "chat_status".
 *
 * @property string $id_message № сообщения
 * @property string $id_user № пользователя
 * @property string $status_message Статус сообщения
 */
class OLDChatStatus extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_message', 'id_user', 'status_message'], 'required'],
            [['id_message', 'id_user'], 'integer'],
            [['status_message'], 'string', 'max' => 20],
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
        ];
    }
}
