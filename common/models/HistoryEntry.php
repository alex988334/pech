<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "history_entry".
 *
 * @property string $id №
 * @property string $id_user № пользователя
 * @property string $action Действие
 * @property string $ip ip адрес
 * @property string $date Дата
 * @property string $time Время
 */
class HistoryEntry extends \yii\db\ActiveRecord
{
    const USER_ENTRY = 'Вход';
    const USER_EXITED = 'Выход';
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'history_entry';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_user', 'date', 'time', 'ip'], 'required'],
            [['id_user'], 'integer'],
            [['ip', 'action'], 'string', 'max' => 20],
            [['date', 'time'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№',
            'id_user' => '№ пользователя',
            'action' => 'Действие',
            'date' => 'Дата',
            'time' => 'Время',
            'ip' => 'ip адрес',
        ];
    }
    
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'id_user']);
    }
    
    public function getRole()
    {
        return $this->hasOne(AuthAssignment::className(), ['user_id' => 'id_user']);
    }
    
    public static function getRelationTablesArray()
    {
        $vid = [];
        $vid['vidAction'] = [self::USER_ENTRY => self::USER_ENTRY, self::USER_EXITED => self::USER_EXITED];
        $vid['vidRole'] = AuthItem::find()->select(['name', 'description'])
                ->indexBy('name')->asArray()->all(); 
        
        return $vid;
    }
}
