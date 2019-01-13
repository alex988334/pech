<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vid_status_history".
 *
 * @property int $id №
 * @property string $name Название
 * @property int $sort Сортировка
 */
class VidStatusHistory extends \yii\db\ActiveRecord
{
    const STATUS_CREATE = 1;
    const STATUS_CHANGE = 2;
    const STATUS_DELETE = 3;
    const STATUS_LOOK = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vid_status_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'name', 'sort'], 'required'],
            [['id', 'sort'], 'integer'],
            [['name'], 'string', 'max' => 30],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№',
            'name' => 'Название',
            'sort' => 'Сортировка',
        ];
    }
}
