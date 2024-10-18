<?php

namespace backend\models;

use Yii;
use common\models\User;
use common\models\VidRegion;
use common\models\AuthAssignment;
use common\models\AuthItem;

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
           // [['phone1'], 'unique'],
        //    [['phone2'], 'unique'],
        //    [['phone3'], 'unique'],
            [['id_region'], 'exist', 'skipOnError' => true, 'targetClass' => VidRegion::className(), 'targetAttribute' => ['id_region' => 'id']],
            [['id_manager'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['id_manager' => 'id']],
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
     *  возращает связанную модель региона
     * @return \yii\db\ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(VidRegion::className(), ['id' => 'id_region']);
    }

    /**
     * возращает связанную модель пользователя
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'id_manager']);
    }
    
    /**
     * возращает связанную модель роли пользователя
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(AuthAssignment::className(), ['user_id' => 'id_manager']);
    }

    /**
     * возращает модель таблицы разрешений менеджера
     * @return \yii\db\ActiveQuery
     */
    public function getManagerTableGrant()
    {
        return $this->hasMany(ManagerTableGrant::className(), ['id_manager' => 'id_manager']);
    }
    
        //  возращает таблицы допустимых значений связанные с этой моделью
    public static function getRelationTablesArray()
    {
        $vid = [];
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->asArray()->all();
        $vid['vidRole'] = AuthItem::find()->select(['description', 'name'])
                ->indexBy('name')->asArray()->all();
        $vid['vidStatus'] = [
            User::STATUS_ACTIVE => 'активен',
            User::STATUS_BLOCKED => 'заблокирован',
            User::STATUS_DELETED => 'удален'
        ];
        return $vid;
    }
}
