<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "chat".
 *
 * @property string $id № чата
 * @property string $autor № автора
 * @property string $alias Название
 * @property string $create_at Дата создания
 * @property string $status Статус чата
 *
 * @property User $autor0
 */
class Chat extends \yii\db\ActiveRecord
{
    /**
     * Константы статусов чата
     */
    const CHAT_ACTIVE = 'active';
    const CHAT_DIACTIVATED = 'diactivated';
    const CHAT_DELETED = 'deleted';
    
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
            [['autor', 'create_at', 'status', 'alias'], 'required'],
            [['autor'], 'integer'],
            [['create_at'], 'safe'],
            [['status'], 'string', 'max' => 20],   
            [['alias'], 'string', 'max' => 30], 
            [['autor'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['autor' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№ чата',
            'autor' => '№ автора',
            'alias' => 'Название',
            'create_at' => 'Дата создания',
            'status' => 'Статус чата',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'autor']);
    }
}
