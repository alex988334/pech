<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vid_shag".
 *
 * @property int $id №
 * @property string $name Шаг выполнения заявки
 * @property int $sort Сортировка
 */
class VidShag extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vid_shag';
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
            'name' => 'Шаг выполнения заявки',
            'sort' => 'Сортировка',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZakaz()
    {
        return $this->hasMany(Zakaz::className(), ['id_shag' => 'id']);
    }
}
