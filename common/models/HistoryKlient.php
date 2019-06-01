<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "history_klient".
 *
 * @property string $id №
 * @property string $date Дата
 * @property string $time Время
 * @property int $id_status_history № статуса изменения
 * @property string $role Роль пользователя
 * @property string $username Имя пользователя
 * @property int $id_user № пользователя
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
class HistoryKlient extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'history_klient';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'time', 'id_status_history', 'role', 'username', 'id_user', 'id_klient', 'imya', 'phone', 'id_region'], 'required'],
            [['date', 'time'], 'safe'],
            [['id_status_history', 'id_user', 'id_klient', 'vozrast', 'id_status_on_off', 'reyting', 'balans', 'id_region', 'old_id'], 'integer'],
            [['role'], 'string', 'max' => 64],
            [['username', 'imya', 'familiya', 'otchestvo'], 'string', 'max' => 50],
            [['phone'], 'string', 'max' => 11],
            
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['id_user' => 'id']],
            [['role'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(), 'targetAttribute' => ['role' => 'name']],
            [['id_status_history'], 'exist', 'skipOnError' => true, 'targetClass' => VidStatusHistory::className(), 'targetAttribute' => ['id_status_history' => 'id']],

            [['id_status_on_off'], 'exist', 'skipOnError' => true, 'targetClass' => VidDefault::className(), 'targetAttribute' => ['id_status_on_off' => 'id']],
            [['id_klient'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['id_klient' => 'id']],
            [['id_region'], 'exist', 'skipOnError' => true, 'targetClass' => VidRegion::className(), 'targetAttribute' => ['id_region' => 'id']],
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
            'old_id' => 'Old ID',
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
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatusHistory()
    {
        return $this->hasOne(VidStatusHistory::className(), ['id' => 'id_status_history']);
    }   
    
    public static function createHistoryModel($id, $status)
    {
        $model = new HistoryKlient();
        $model->setAttributes(Klient::find()->where(['id' => $id])->asArray()->limit(1)->one());
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
        $vid['vidStatusHistory'] = VidStatusHistory::find()->select(['id', 'name'])->asArray()->all();          
        $vid['vidStatusOnOff'] = VidDefault::find()->select(['id', 'name'])->asArray()->all();        
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->asArray()->all();
        
        return $vid;
    }
}
