<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vid_work".
 *
 * @property int $id №
 * @property string $name Вид работ
 * @property int $sort Сортировка
 */
class VidWork extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vid_work';
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
            'name' => 'Вид работ',
            'sort' => 'Сортировка',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZakaz()
    {
        return $this->hasMany(Zakaz::className(), ['id_vid_work' => 'id']);
    }
    
    public function getMasterWorkNavik()
    {
        return $this->hasMany(MasterWorkNavik::className(), ['id_vid_work' => 'id']);
    }
}
