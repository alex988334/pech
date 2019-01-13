<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vid_ocenka".
 *
 * @property int $id №
 * @property string $name Оценка
 * @property int $sort Сортировка
 */
class VidOcenka extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vid_ocenka';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'sort'], 'required'],
            [['sort'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['sort'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№',
            'name' => 'Оценка',
            'sort' => 'Сортировка',
        ];
    }
    
    public function getZakaz()
    {
        return $this->hasMany(Zakaz::className(), ['id_ocenka' => 'id']);
    }
}
