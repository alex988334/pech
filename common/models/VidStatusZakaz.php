<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vid_status_zakaz".
 *
 * @property int $id №
 * @property string $name Статус заявки
 * @property int $visibility_master Видимость мастером
 */
class VidStatusZakaz extends \yii\db\ActiveRecord
{
    const ORDER_EXECUTES = 0;
    const ORDER_AVAILABLE = 1;
    const ORDER_NEW = 2;
    const ORDER_UNAVAILABLE = 3;
    const ORDER_CANCELLED = 4;
    const ORDER_EXECUTED = 5;
    const ORDER_MASTER_INABILITY = 6;
    const ORDER_REQUEST_REJECTION = 7;
        
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vid_status_zakaz';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['visibility_master'], 'integer'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№',
            'name' => 'Статус заявки',
            'visibility_master' => 'Видимость мастером',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZakaz()
    {
        return $this->hasMany(Zakaz::className(), ['id_status_zakaz' => 'id']);
    }
}
