<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "klient_vs_zakaz".
 *
 * @property string $id №
 * @property string $id_klient № клиента
 * @property string $id_zakaz № заявки
 */
class KlientVsZakaz extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'klient_vs_zakaz';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_klient', 'id_zakaz'], 'required'],
            [['id_klient', 'id_zakaz'], 'integer'],
            [['id_klient'], 'exist', 'skipOnError' => true, 'targetClass' => Klient::className(), 'targetAttribute' => ['id_klient' => 'id_klient']],
            [['id_zakaz'], 'exist', 'skipOnError' => true, 'targetClass' => Zakaz::className(), 'targetAttribute' => ['id_zakaz' => 'id']],
            [['id_klient', 'id_zakaz'], 'unique', 'targetAttribute' => ['id_klient', 'id_zakaz']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№',
            'id_klient' => '№ клиента',
            'id_zakaz' => '№ заявки',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKlient()
    {
        return $this->hasOne(Klient::className(), ['id_klient' => 'id_klient']);
    }
    
    public function getRegion()
    {
        return $this->hasOne(VidRegion::className(), ['id' => 'id_region'])->via('klient');
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'id_klient'])->via('klient');
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZakaz()
    {
        return $this->hasOne(Zakaz::className(), ['id' => 'id_zakaz']);
    }
    
    public function getVidWork()
    {
        return $this->hasOne(VidWork::className(), ['id' => 'id_vid_work'])->via('zakaz');
    }
}
