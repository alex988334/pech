<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "chat_black_list".
 *
 * @property string $blocking № пользователя
 * @property string $locked № заблокированного пользователя
 * @property string $date Дата
 * @property string $time Время
 *
 * @property User $blocking0
 * @property User $locked0
 */
class ChatBlackList extends \yii\db\ActiveRecord
{
    const BLACK_LIST_ACTIVE = 1;
    const BLACK_LIST_BLOCKED = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat_black_list';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['blocking', 'locked', 'date', 'time'], 'required'],
            [['blocking', 'locked'], 'integer'],
            [['date', 'time'], 'safe'],
            [['blocking', 'locked'], 'unique', 'targetAttribute' => ['blocking', 'locked']],
            [['blocking'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['blocking' => 'id']],
            [['locked'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['locked' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'blocking' => '№ пользователя',
            'locked' => '№ заблокированного пользователя',
            'date' => 'Дата',
            'time' => 'Время',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBlockingUser()
    {
        return $this->hasOne(User::className(), ['id' => 'blocking']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLockedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'locked']);
    }
}
