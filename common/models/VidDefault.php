<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vid_default".
 *
 * @property int $id №
 * @property string $name Название
 * @property int $sort Сортировка
 */
class VidDefault extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vid_default';
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
            'name' => 'Название',
            'sort' => 'Сортировка',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
  /*  public function getManagerTableSettings()
    {
        return $this->hasMany(ManagerTableSettings::className(), ['visibility' => 'id']);
    }*/

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMasters()
    {
        return $this->hasMany(Master::className(), ['id_status_on_off' => 'id']);
    }
}
