<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vid_region".
 *
 * @property int $id №
 * @property string $name Регион или населенный пункт
 * @property int $parent_id № родительского региона
 * @property double $dolgota Долгота
 * @property double $shirota Широта
 */
class VidRegion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vid_region';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'parent_id', 'dolgota', 'shirota'], 'required'],
            [['parent_id'], 'integer'],
            [['dolgota', 'shirota'], 'number'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№',
            'name' => 'Регион или населенный пункт',
            'parent_id' => '№ родительского региона',
            'dolgota' => 'Долгота',
            'shirota' => 'Широта',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKlient()
    {
        return $this->hasMany(Klient::className(), ['id_region' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasMany(Manager::className(), ['id_region' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMaster()
    {
        return $this->hasMany(Master::className(), ['id_region' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZakaz()
    {
        return $this->hasMany(Zakaz::className(), ['id_region' => 'id']);
    }
    
    public static function getRelationTablesArray()
    {
        $model = VidRegion::find()->select(['id', 'name'])->where(['parent_id' => 0])->all();
        
        return ['vidRegion' => $model];
    }
}
