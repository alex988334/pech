<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "manager_table".
 *
 * @property int $id №
 * @property string $name Название поля или таблицы
 * @property int $parent № родительской категории
 * @property string $alias Псевдоним
 */
class ManagerTable extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'manager_table';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['parent'], 'integer'],
            [['name', 'alias'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№',
            'name' => 'Название поля или таблицы',
            'parent' => '№ родительской категории',
            'alias' => 'Псевдоним',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManagerTableGrant()
    {
        return $this->hasMany(ManagerTableGrant::className(), ['id_table_field' => 'id']);
    }
}
