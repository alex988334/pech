<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "history_master".
 *
 * @property string $id №
 * @property string $date Дата
 * @property string $time Время
 * @property int $id_status_history № статуса изменения
 * @property string $role Роль пользователя
 * @property string $username Имя пользователя
 * @property string $id_user № пользователя
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
 * @property string $old_id № в старой бд
 */
class HistoryMaster extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'history_master';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'time', 'id_status_history', 'role', 'username', 'id_user', 'id_master', 'familiya', 'imya', 'reyting', 'data_registry', 'phone', 'id_region'], 'required'],
            [['date', 'time', 'data_registry', 'data_unregistry'], 'safe'],
            [['id_status_history', 'id_user', 'id_master', 'id_status_on_off', 'vozrast', 'staj', 'reyting', 'id_status_work', 'balans', 'id_region', 'limit_zakaz', 'old_id'], 'integer'],
            [['role'], 'string', 'max' => 64],
            [['username', 'familiya', 'imya', 'otchestvo'], 'string', 'max' => 50],
            [['phone'], 'string', 'max' => 11],
            [['mesto_jitelstva', 'mesto_raboti'], 'string', 'max' => 100],
            
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['id_user' => 'id']],
            [['role'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(), 'targetAttribute' => ['role' => 'name']],
            [['id_status_history'], 'exist', 'skipOnError' => true, 'targetClass' => VidStatusHistory::className(), 'targetAttribute' => ['id_status_history' => 'id']],

            [['id_region'], 'exist', 'skipOnError' => true, 'targetClass' => VidRegion::className(), 'targetAttribute' => ['id_region' => 'id']],
            [['id_status_on_off'], 'exist', 'skipOnError' => true, 'targetClass' => VidDefault::className(), 'targetAttribute' => ['id_status_on_off' => 'id']],
            [['id_status_work'], 'exist', 'skipOnError' => true, 'targetClass' => VidStatusWork::className(), 'targetAttribute' => ['id_status_work' => 'id']],
            [['id_master'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['id_master' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№',
            'date' => 'Дата',
            'time' => 'Время',
            'id_status_history' => '№ статуса изменения',
            'role' => 'Роль пользователя',
            'username' => 'Имя пользователя',
            'id_user' => '№ пользователя',
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
            'old_id' => '№ в старой бд',
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
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatusHistory()
    {
        return $this->hasOne(VidStatusHistory::className(), ['id' => 'id_status_history']);
    }
    
    public static function createHistoryModel($id, $status)
    {
        $model = new HistoryMaster();
        $model->setAttributes(Master::find()->where(['id' => $id])->asArray()->limit(1)->one());
        $model->date = date('Y-m-d');
        $model->time = date('H:i:s');
        $model->id_status_history = $status;
        $model->id_user = Yii::$app->user->getId();
        $model->role = Yii::$app->session->get('role');
        $model->username = User::find()->select(['username'])->where(['id' => $model->id_user])->scalar();  
        
        return $model;
    }
    
    public static function getRelationTablesArray()
    {
        $vid = [];
        $vid['vidStatusHistory'] = VidStatusHistory::find()->asArray()->all();
        $vid['vidStatusOnOff'] = VidDefault::find()->asArray()->all();
        $vid['vidStatusWork'] = VidStatusWork::find()->asArray()->all();
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->asArray()->all();
        
        return $vid;
    }
}
