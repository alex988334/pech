<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "history_zakaz".
 *
 * @property string $id № Заявки
 * @property string $date Дата
 * @property string $time Время
 * @property int $id_status_history № статуса изменения
 * @property string $role Роль пользователя
 * @property string $username Имя пользователя
 * @property string $id_user Номер пользователя
 * @property string $id_zakaz Номер заявки
 * @property int $id_vid_work № вида работ
 * @property int $id_navik № планка требуемого навыка
 * @property string $name Название
 * @property string $cena Цена
 * @property string $opisanie Описание
 * @property string $reyting_start Планка рейтинга
 * @property string $zametka Заметка
 * @property string $gorod Город
 * @property string $poselok Поселок
 * @property string $ulica Улица
 * @property int $dom Дом
 * @property int $kvartira Квартира
 * @property int $id_status_zakaz № статуса
 * @property int $id_shag № шага выполнения
 * @property string $data_registry Дата регистрации
 * @property string $data_start Дата начала работ
 * @property string $data_end Дата завершения работ
 * @property double $dolgota Долгота
 * @property double $shirota Широта
 * @property double $dolgota_change Долгота
 * @property double $shirota_change Широта
 * @property string $image Файл изображения
 * @property int $id_region № региона
 * @property int $id_ocenka № оценки
 * @property string $otziv Отзыв клиента
 */
class HistoryZakaz extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'history_zakaz';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'time', 'id_status_history', 'role', 'username', 'id_user', 'id_zakaz', 'id_vid_work', 'id_navik', 'name', 'cena', 'opisanie', 'reyting_start', 'data_registry', 'data_start', 'data_end', 'id_region'], 'required'],
            [['date', 'time', 'data_registry', 'data_start', 'data_end'], 'safe'],
            [['id_status_history', 'id_user', 'id_zakaz', 'id_vid_work', 'id_navik', 'cena', 'reyting_start', 'dom', 'kvartira', 'id_status_zakaz', 'id_shag', 'id_region', 'id_ocenka'], 'integer'],
            [['dolgota', 'shirota', 'dolgota_change', 'shirota_change'], 'number'],
            [['role'], 'string', 'max' => 64],
            [['username', 'gorod', 'poselok', 'ulica', 'image'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 100],
            [['opisanie'], 'string', 'max' => 500],
            [['zametka'], 'string', 'max' => 255],
            [['otziv'], 'string', 'max' => 1000],
            
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['id_user' => 'id']],
            [['role'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(), 'targetAttribute' => ['role' => 'name']],
            [['id_status_history'], 'exist', 'skipOnError' => true, 'targetClass' => VidStatusHistory::className(), 'targetAttribute' => ['id_status_history' => 'id']],
            
            [['id_navik'], 'exist', 'skipOnError' => true, 'targetClass' => VidNavik::className(), 'targetAttribute' => ['id_navik' => 'id']],
            [['id_status_zakaz'], 'exist', 'skipOnError' => true, 'targetClass' => VidStatusZakaz::className(), 'targetAttribute' => ['id_status_zakaz' => 'id']],
            [['id_shag'], 'exist', 'skipOnError' => true, 'targetClass' => VidShag::className(), 'targetAttribute' => ['id_shag' => 'id']],
            [['id_region'], 'exist', 'skipOnError' => true, 'targetClass' => VidRegion::className(), 'targetAttribute' => ['id_region' => 'id']],
            [['id_vid_work'], 'exist', 'skipOnError' => true, 'targetClass' => VidWork::className(), 'targetAttribute' => ['id_vid_work' => 'id']],
            [['id_ocenka'], 'exist', 'skipOnError' => true, 'targetClass' => VidOcenka::className(), 'targetAttribute' => ['id_ocenka' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№ Заявки',
            'date' => 'Дата',
            'time' => 'Время',
            'id_status_history' => '№ статуса изменения',
            'role' => 'Роль пользователя',
            'username' => 'Имя пользователя',
            'id_user' => 'Номер пользователя',
            'id_zakaz' => 'Номер заявки',
            'id_vid_work' => '№ вида работ',
            'id_navik' => '№ планка требуемого навыка',
            'name' => 'Название',
            'cena' => 'Цена',
            'opisanie' => 'Описание',
            'reyting_start' => 'Планка рейтинга',
            'zametka' => 'Заметка',
            'gorod' => 'Город',
            'poselok' => 'Поселок',
            'ulica' => 'Улица',
            'dom' => 'Дом',
            'kvartira' => 'Квартира',
            'id_status_zakaz' => '№ статуса',
            'id_shag' => '№ шага выполнения',
            'data_registry' => 'Дата регистрации',
            'data_start' => 'Дата начала работ',
            'data_end' => 'Дата завершения работ',
            'dolgota' => 'Долгота',
            'shirota' => 'Широта',
            'dolgota_change' => 'Долгота',
            'shirota_change' => 'Широта',
            'image' => 'Файл изображения',
            'id_region' => '№ региона',
            'id_ocenka' => '№ оценки',
            'otziv' => 'Отзыв клиента',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKlientVsZakaz()
    {
        return $this->hasMany(KlientVsZakaz::className(), ['id_zakaz' => 'id']);
    }
    
    public function getKlient()
    {
        return $this->hasOne(Klient::className(), ['id_klient' => 'id_klient'])->via('klientVsZakaz');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMasterVsZakaz()
    {
        return $this->hasMany(MasterVsZakaz::className(), ['id_zakaz' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNavik()
    {
        return $this->hasOne(VidNavik::className(), ['id' => 'id_navik']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
        public function getStatusZakaz()
    {
        return $this->hasOne(VidStatusZakaz::className(), ['id' => 'id_status_zakaz']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShag()
    {
        return $this->hasOne(VidShag::className(), ['id' => 'id_shag']);
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
    public function getVidWork()
    {
        return $this->hasOne(VidWork::className(), ['id' => 'id_vid_work']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOcenka()
    {
        return $this->hasOne(VidOcenka::className(), ['id' => 'id_ocenka']);
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
        $model = new HistoryZakaz();
        $model->setAttributes(Zakaz::find()->where(['id' => $id])->asArray()->limit(1)->one());
        $model->id_zakaz = $id;
        $model->id = null;
        $model->date = date('Y-m-d');
        $model->time = date('H:i:s');
        $model->id_status_history = $status;
        $model->id_user = Yii::$app->user->getId();
        $model->role = Yii::$app->session->get('role');
        $model->username = User::find()->select(['username'])->where(['id' => $model->id_user])->scalar();  
        
        return $model;
    }
}
