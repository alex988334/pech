<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "klient".
 *
 * @property string $id №
 * @property string $id_klient № клиента
 * @property string $imya Имя
 * @property string $familiya Фамилия
 * @property string $otchestvo Отчество
 * @property int $vozrast Возраст
 * @property string $phone Телефон
 * @property int $id_status_on_off № статуса подключения
 * @property string $reyting Рейтинг
 * @property string $balans Баланс
 * @property int $id_region № региона
 * @property string $old_id
 */
class Klient extends \yii\db\ActiveRecord
{
    const SCENARIO_UPDATE_MANAGER = 'update_manager';
    const SCENARIO_UPDATE_HEAD_MANAGER = 'update_head_manager';
    const SCENARIO_CREATE = 'create';
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'klient';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_klient', 'imya', 'phone', 'id_region'], 'required'],
            [['id_klient', 'vozrast', 'id_status_on_off', 'reyting', 'balans', 'id_region', /* 'old_id'*/ ], 'integer'],
            [['imya', 'familiya', 'otchestvo'], 'string', 'max' => 50],
            [['phone'], 'string', 'max' => 11],
            [['id_klient'], 'unique'],
            [['phone'], 'unique'],
    //        [['old_id'], 'unique'],
            [['id_status_on_off'], 'exist', 'skipOnError' => true, 'targetClass' => VidDefault::className(), 'targetAttribute' => ['id_status_on_off' => 'id']],
            [['id_klient'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['id_klient' => 'id']],
            [['id_region'], 'exist', 'skipOnError' => true, 'targetClass' => VidRegion::className(), 'targetAttribute' => ['id_region' => 'id']],
        ];
    }
    
    public function scenarios() {
        parent::scenarios();        
        
        return [
            
            self::SCENARIO_UPDATE_HEAD_MANAGER => [
                'imya', 'familiya', 'otchestvo', 'vozrast', 'id_status_on_off', 
                'balans', 'phone'
                ],            
            
            self::SCENARIO_UPDATE_MANAGER => [
                'imya', 'familiya', 'otchestvo', 'vozrast', 'id_status_on_off'
            ],  
            self::SCENARIO_CREATE => [
                'id_klient', 'imya', 'familiya', 'otchestvo', 'vozrast', 'phone'
            ],
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
            'imya' => 'Имя',
            'familiya' => 'Фамилия',
            'otchestvo' => 'Отчество',
            'vozrast' => 'Возраст',
            'phone' => 'Телефон',
            'id_status_on_off' => '№ статуса подключения',
            'reyting' => 'Рейтинг',
            'balans' => 'Баланс',
            'id_region' => '№ региона',
    //        'old_id' => 'Old ID',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'id_klient']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(VidRegion::className(), ['id' => 'id_region']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKlientVsZakaz()
    {
        return $this->hasMany(KlientVsZakaz::className(), ['id_klient' => 'id_klient']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatusOnOff()
    {
        return $this->hasOne(VidDefault::className(), ['id' => 'id_status_on_off']);
    }
    
}
