<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "master".
 *
 * @property string $id №
 * @property string $id_master № мастера
 * @property string $familiya Фамилия
 * @property string $imya Имя
 * @property string $otchestvo Отчество
 * @property int $id_status_on_off № статуса подключения
 * @property int $vozrast Возраст
 * @property int $staj Стаж
 * @property string $reyting Рейтинг
 * @property int $id_status_work № статуса работника
 * @property string $data_registry Дата регистрации
 * @property string $data_unregistry Дата снятия регистрации
 * @property string $phone Телефон
 * @property string $mesto_jitelstva Место жительства
 * @property string $mesto_raboti Место работы
 * @property string $balans Баланс
 * @property int $id_region № региона
 * @property int $limit_zakaz Лимит одновременных заявок
 * @property string $old_id
 */
class Master extends \yii\db\ActiveRecord
{
    const SCENARIO_UPDATE_MANAGER = 'update_manager';
    const SCENARIO_UPDATE_HEAD_MANAGER = 'update_head_manager';
    const SCENARIO_CREATE = 'create';
    const SCENARIO_RECOVERY = 'recovery';
    
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'master';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_master', 'familiya', 'imya', 'reyting', 'data_registry', 'phone', 'id_region'], 'required'],
            [['id_master', 'id_status_on_off', 'vozrast', 'staj', 'reyting', 'id_status_work', 'balans', 'id_region', 'limit_zakaz', /* 'old_id'*/ ], 'integer'],
            [['data_registry', 'data_unregistry'], 'safe'],
            [['familiya', 'imya', 'otchestvo'], 'string', 'max' => 50],
            [['phone'], 'string', 'max' => 11],
            [['mesto_jitelstva', 'mesto_raboti'], 'string', 'max' => 100],
            [['id_master'], 'unique'],
            [['phone'], 'unique'],
     //       [['old_id'], 'unique'],
            [['id_region'], 'exist', 'skipOnError' => true, 'targetClass' => VidRegion::className(), 'targetAttribute' => ['id_region' => 'id']],
            [['id_status_on_off'], 'exist', 'skipOnError' => true, 'targetClass' => VidDefault::className(), 'targetAttribute' => ['id_status_on_off' => 'id']],
            [['id_status_work'], 'exist', 'skipOnError' => true, 'targetClass' => VidStatusWork::className(), 'targetAttribute' => ['id_status_work' => 'id']],
            [['id_master'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['id_master' => 'id']],
   //        
        ];
    }
    
    public function scenarios() {
        parent::scenarios();        
        
        return [
            
            self::SCENARIO_UPDATE_HEAD_MANAGER => [
                'familiya', 'imya', 'otchestvo', 'id_status_on_off', 'vozrast', 'staj', 
                'id_status_work', 'data_unregistry', 'mesto_jitelstva', 'mesto_raboti',
                'phone', 'balans', 'id_region', 'reyting'
                ],            
            
            self::SCENARIO_UPDATE_MANAGER => [
                'familiya', 'imya', 'otchestvo', 'id_status_on_off', 'vozrast', 'staj', 
                'id_status_work', 'data_unregistry', 'mesto_jitelstva', 'mesto_raboti', 'id_region'
            ], 
            self::SCENARIO_CREATE => [
                'id_master', 'familiya', 'imya', 'otchestvo', 'vozrast', 'staj', 
                'id_status_work', 'phone', 'mesto_jitelstva', 'mesto_raboti'                
            ],
            self::SCENARIO_RECOVERY => [
                'id', 'id_master', 'familiya', 'imya', 'otchestvo', 'id_status_on_off',
                'vozrast', 'staj', 'reyting', 'id_status_work', 'data_registry',
                'data_unregistry', 'phone', 'mesto_jitelstva', 'mesto_raboti', 'balans',
                'id_region', 'limit_zakaz', 'old_id',
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
            'id_master' => '№ мастера',
            'familiya' => 'Фамилия',
            'imya' => 'Имя',
            'otchestvo' => 'Отчество',
            'id_status_on_off' => '№ статуса подключения',
            'vozrast' => 'Возраст',
            'staj' => 'Стаж',
            'reyting' => 'Рейтинг',
            'id_status_work' => '№ статуса работника',
            'data_registry' => 'Дата регистрации',
            'data_unregistry' => 'Дата снятия регистрации',
            'phone' => 'Телефон',
            'mesto_jitelstva' => 'Место жительства',
            'mesto_raboti' => 'Место работы',
            'balans' => 'Баланс',
            'id_region' => '№ региона',
            'limit_zakaz' => 'Лимит одновременных заявок',
            'old_id' => 'Old ID',
        ];
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
    public function getStatusOnOff()
    {
         return $this->hasOne(VidDefault::className(), ['id' => 'id_status_on_off']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatusWork()
    {
        return $this->hasOne(VidStatusWork::className(), ['id' => 'id_status_work']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'id_master']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
  /*  public function getLimitZakaz()
    {
        return $this->hasOne(VidLimitZakaz::className(), ['id' => 'limit_zakaz']);
    }*/

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMasterWorkNavik()
    {
        return $this->hasMany(MasterWorkNavik::className(), ['id_master' => 'id_master']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMasterVsZakaz()
    {
        return $this->hasMany(MasterVsZakaz::className(), ['id_master' => 'id_master']); 
    }
    
    public function getZakaz()
    {
        return $this->hasMany(Zakaz::className(), ['id' => 'id_zakaz'])->via('masterVsZakaz');
    }
    
    public function createNew()
    {
        if (!$this->validate()) {
            return null;
        }
        if (!$this->save()) {
            return false;
        } 
        return true;
    }
    
    public static function getRelationTablesArray()
    {
        $vid = [];
        $vid['vidStatusOnOff'] = VidDefault::find()->indexBy('id')->asArray()->all();
        $vid['vidStatusWork'] = VidStatusWork::find()->indexBy('id')->asArray()->all();
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->indexBy('id')->asArray()->all();
        
        return $vid;
    }
}
