<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vid_status_work".
 *
 * @property int $id №
 * @property string $name Статус работника
 * @property int $sort Сортировка
 */
class VidStatusWork extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vid_status_work';
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
            'name' => 'Статус работника',
            'sort' => 'Сортировка',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMaster()
    {
        return $this->hasMany(Master::className(), ['id_status_work' => 'id']);
    }
}
