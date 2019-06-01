<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vid_change_parametr".
 *
 * @property int $id №
 * @property int $reyting_add Добавляемый рейтинг за заявку
 * @property int $reyting_delete Снимаемый рейтинг за отказ от заявки
 * @property int $balans_add Процент баланса возвращаемый после отказа от заявки
 * @property int $balans_delete Процент баланса снимаемый за заявку
 */
class VidChangeParametr extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vid_change_parametr';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['reyting_add', 'reyting_delete', 'balans_add', 'balans_delete'], 'required'],
            [['reyting_add', 'reyting_delete', 'balans_add', 'balans_delete'], 'integer'],
            [['name'], 'string'],
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
            'reyting_add' => 'Добавляемый рейтинг за заявку',
            'reyting_delete' => 'Снимаемый рейтинг за отказ от заявки',
            'balans_add' => 'Процент баланса возвращаемый после отказа от заявки',
            'balans_delete' => 'Процент баланса снимаемый за заявку',
        ];
    }
}
