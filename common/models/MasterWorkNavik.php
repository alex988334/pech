<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "master_work_navik".
 *
 * @property string $id №
 * @property string $id_master № мастера
 * @property int $id_vid_work № вида работ
 * @property int $id_vid_navik № вида навыка
 */
class MasterWorkNavik extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'master_work_navik';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_master', 'id_vid_work', 'id_vid_navik'], 'required'],
            [['id_master', 'id_vid_work', 'id_vid_navik'], 'integer'],
            [['id_master'], 'exist', 'skipOnError' => true, 'targetClass' => Master::className(), 'targetAttribute' => ['id_master' => 'id_master']],
            [['id_vid_work'], 'exist', 'skipOnError' => true, 'targetClass' => VidWork::className(), 'targetAttribute' => ['id_vid_work' => 'id']],
            [['id_vid_navik'], 'exist', 'skipOnError' => true, 'targetClass' => VidNavik::className(), 'targetAttribute' => ['id_vid_navik' => 'id']],
            [['id_master', 'id_vid_work'], 'unique', 'targetAttribute' => ['id_master', 'id_vid_work']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№',
            'id_master' => '№ мастера',
            'id_vid_work' => '№ вида работ',
            'id_vid_navik' => '№ вида навыка',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMaster()
    {
        return $this->hasOne(Master::className(), ['id_master' => 'id_master']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVidWork()
    {
        return $this->hasOne(VidWork::className(), ['id' => 'id_vid_work']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVidNavik()
    {
        return $this->hasOne(VidNavik::className(), ['id' => 'id_vid_navik']);
    }
    
    public static function getRelationTablesArray()
    {
        $vid = [];
        $vid['vidWork'] = VidWork::find()->asArray()->all();
        $vid['vidNavik'] = VidNavik::find()->asArray()->all();
        
        return $vid;
    }
}
