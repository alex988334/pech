<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "master_vs_zakaz".
 *
 * @property string $id №
 * @property string $id_master № мастера
 * @property string $id_zakaz № заявки
 */
class MasterVsZakaz extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'master_vs_zakaz';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_master', 'id_zakaz'], 'required'],
            [['id_master', 'id_zakaz'], 'integer'],
            [['id_master'], 'exist', 'skipOnError' => true, 'targetClass' => Master::className(), 'targetAttribute' => ['id_master' => 'id_master']],
            [['id_zakaz'], 'exist', 'skipOnError' => true, 'targetClass' => Zakaz::className(), 'targetAttribute' => ['id_zakaz' => 'id']],
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
            'id_zakaz' => '№ заявки',
        ];
    }
    
    public function getMaster()
    {
        return $this->hasOne(Master::className(), ['id_master' => 'id_master']);
    }
    
    public function getRegion()
    {
        return $this->hasOne(VidRegion::className(), ['id' => 'id_region'])->via('master');
    }
    
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'id_master']);
    }
    
    public function getZakaz()
    {
        return $this->hasOne(Zakaz::className(), ['id' => 'id_zakaz']);
    }
    
    public function getVidWork()
    {
        return $this->hasOne(VidWork::className(), ['id' => 'id_vid_work'])->via('zakaz');  
    }
    
    public function getStatusZakaz()
    {
        return $this->hasOne(VidStatusZakaz::className(), ['id' => 'id_status_zakaz'])->via('zakaz');
    }
    
    public function getShag()
    {
        return $this->hasOne(VidShag::className(), ['id' => 'id_shag'])->via('zakaz');
    }

    public function getKlientVsZakaz()
    {
        return $this->hasOne(KlientVsZakaz::className(), ['id_zakaz' => 'id_zakaz']);
    }
    
    public function getKlient()
    {
        return $this->hasOne(Klient::className(), ['id_klient' => 'id_klient'])->via('klientVsZakaz');
    }
}
