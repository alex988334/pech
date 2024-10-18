<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "manager".
 *
 * @property int $id №
 * @property string $id_manager № менеджера
 * @property string $familiya Фамилия
 * @property string $imya Имя
 * @property string $otchestvo Отчество
 * @property int $id_region № региона
 * @property string $phone1 Телефон 1
 * @property string $phone2 Телефон 2
 * @property string $phone3 Телефон 3
 */
class Manager extends \yii\db\ActiveRecord
{
    const SCENARIO_UPDATE_MANAGER = 'update_manager';
    const SCENARIO_UPDATE_HEAD_MANAGER = 'update_head_manager';
    const SCENARIO_UPDATE_ADMIN = 'update_admin';
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'manager';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_manager', 'familiya', 'imya', 'otchestvo', 'id_region', 'phone1'], 'required'],
            [['id_manager', 'id_region'], 'integer'],
            [['familiya', 'imya', 'otchestvo'], 'string', 'max' => 50],
            [['phone1', 'phone2', 'phone3'], 'string', 'max' => 11],
            [['id_manager'], 'unique'],
            [['phone1'], 'unique'],
            [['phone2'], 'unique'],
            [['phone3'], 'unique'],
            [['id_region'], 'exist', 'skipOnError' => true, 'targetClass' => VidRegion::className(), 'targetAttribute' => ['id_region' => 'id']],
            [['id_manager'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['id_manager' => 'id']],
        ];
    }
    
    public function scenarios() {
        parent::scenarios();        
        
        return [
            
            self::SCENARIO_UPDATE_ADMIN => ['imya', 'familiya', 'otchestvo', 'phone1', 'phone2', 'phone3'],
            
            self::SCENARIO_UPDATE_HEAD_MANAGER => [
                'imya', 'familiya', 'otchestvo', 'phone1', 'phone2', 'phone3'
                ],            
            
            self::SCENARIO_UPDATE_MANAGER => [
                'imya', 'familiya', 'otchestvo', 'phone1', 'phone2', 'phone3'
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
            'id_manager' => '№ менеджера',
            'familiya' => 'Фамилия',
            'imya' => 'Имя',
            'otchestvo' => 'Отчество',
            'id_region' => '№ региона',
            'phone1' => 'Телефон 1',
            'phone2' => 'Телефон 2',
            'phone3' => 'Телефон 3',
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
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'id_manager']);
    }
    
    public function getRole()
    {
        return $this->hasOne(AuthAssignment::className(), ['user_id' => 'id_manager']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManagerTableGrant()
    {
        return $this->hasMany(ManagerTableGrant::className(), ['id_manager' => 'id_manager']);
    }
    
    public static function getRelationTablesArray()
    {
        $vid = [];
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->indexBy('id')->asArray()->all();
        
        return $vid;
    }
}
