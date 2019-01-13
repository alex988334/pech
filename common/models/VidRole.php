<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vid_role".
 *
 * @property int $id №
 * @property string $value Значение константы
 * @property string $name Название роли
 * @property int $sort Сортировка
 */
class VidRole extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vid_role';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['value', 'name', 'sort'], 'required'],
            [['sort'], 'integer'],
            [['value', 'name'], 'string', 'max' => 50],
            [['value'], 'unique'],
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
            'value' => 'Значение константы',
            'name' => 'Название роли',
            'sort' => 'Сортировка',
        ];
    }
}
