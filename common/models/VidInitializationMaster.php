<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vid_initialization_master".
 *
 * @property int $id №
 * @property string $name Вариант инициализации мастера
 * @property int $start_reyting Начальный рейтинг
 * @property int $start_balans начальный баланс
 * @property int $limit_zakaz Лимит одновременных заявок
 * @property int $sort Сортировка
 */
class VidInitializationMaster extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vid_initialization_master';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'start_reyting', 'start_balans', 'limit_zakaz', 'sort'], 'required'],
            [['start_reyting', 'start_balans', 'limit_zakaz', 'sort'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['name'], 'unique'],
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
            'name' => 'Вариант инициализации мастера',
            'start_reyting' => 'Начальный рейтинг',
            'start_balans' => 'начальный баланс',
            'limit_zakaz' => 'Лимит одновременных заявок',
            'sort' => 'Сортировка',
        ];
    }
}
