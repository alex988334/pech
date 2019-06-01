<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "chat_user".
 *
 * @property string $id_chat № чата
 * @property string $id_user № пользователя
 *
 * @property Chat $chat
 */
class ChatUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_chat', 'id_user'], 'required'],
            [['id_chat', 'id_user'], 'integer'],
            [['client_hash'], 'string', 'max' => 255],
            [['id_chat', 'id_user'], 'unique', 'targetAttribute' => ['id_chat', 'id_user']],
            [['id_chat'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::className(), 'targetAttribute' => ['id_chat' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_chat' => '№ чата',
            'id_user' => '№ пользователя',
            'client_hash' => 'Идентификатор клиента',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::className(), ['id' => 'id_chat']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBlackList()
    {
        return $this->hasMany(ChatBlackList::className(), ['blocking' => 'id_user']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'id_user']);
    }
}
