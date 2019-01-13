<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vid_navik".
 *
 * @property int $id №
 * @property string $name Навык
 * @property int $sort Сортировка
 */
class VidNavik extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vid_navik';
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
            'name' => 'Навык',
            'sort' => 'Сортировка',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMasterWorkNavik()
    {
        return $this->hasMany(MasterWorkNavik::className(), ['id_vid_navik' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZakaz()
    {
        return $this->hasMany(Zakaz::className(), ['id_navik' => 'id']);
    }
}
