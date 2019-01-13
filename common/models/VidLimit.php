<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vid_limit".
 *
 * @property int $id №
 * @property string $name Статус лимита
 * @property int $sort Сортировка
 */
class VidLimit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vid_limit';
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
            'name' => 'Статус лимита',
            'sort' => 'Сортировка',
        ];
    }
}
