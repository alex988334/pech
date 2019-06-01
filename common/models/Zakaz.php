<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "zakaz".
 *
 * @property string $id № Заявки
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
class Zakaz extends \yii\db\ActiveRecord
{    
    const SCENARIO_UPDATE_MANAGER = 'update_manager';
    const SCENARIO_UPDATE_MASTER = 'update_master';
    const SCENARIO_UPDATE_HEAD_MANAGER = 'update_head_manager';
    const SCENARIO_CREATE = 'create';
    const SCENARIO_RECOVERY = 'recovery';
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'zakaz';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
               
        return [
            [['id_vid_work', 'id_navik', 'name', 'cena', 'opisanie', 'reyting_start', 'data_registry', 'data_start', 'data_end', 'id_region'], 'required'],
            [['id_vid_work', 'id_navik', 'cena', 'reyting_start', 'dom', 'kvartira', 'id_status_zakaz', 'id_shag', 'id_region', 'id_ocenka'], 'integer'],
            [['data_registry', 'data_start', 'data_end'], 'safe'],
            [['dolgota', 'shirota', 'dolgota_change', 'shirota_change'], 'number'],
            [['name'], 'string', 'max' => 100],
            [['opisanie'], 'string', 'max' => 500],
            [['zametka'], 'string', 'max' => 255],
            [['gorod', 'poselok', 'ulica', 'image'], 'string', 'max' => 50],
            [['otziv'], 'string', 'max' => 1000],
            
    /*недавно*/        [['id'], 'unique', 'targetAttribute' => ['id']],
            
            [['id_navik'], 'exist', 'skipOnError' => true, 'targetClass' => VidNavik::className(), 'targetAttribute' => ['id_navik' => 'id']],
            [['id_status_zakaz'], 'exist', 'skipOnError' => true, 'targetClass' => VidStatusZakaz::className(), 'targetAttribute' => ['id_status_zakaz' => 'id']],
            [['id_shag'], 'exist', 'skipOnError' => true, 'targetClass' => VidShag::className(), 'targetAttribute' => ['id_shag' => 'id']],
            [['id_region'], 'exist', 'skipOnError' => true, 'targetClass' => VidRegion::className(), 'targetAttribute' => ['id_region' => 'id']],
            [['id_vid_work'], 'exist', 'skipOnError' => true, 'targetClass' => VidWork::className(), 'targetAttribute' => ['id_vid_work' => 'id']],
            [['id_ocenka'], 'exist', 'skipOnError' => true, 'targetClass' => VidOcenka::className(), 'targetAttribute' => ['id_ocenka' => 'id']],
        
        ];
    }
    
    public function scenarios() {
        parent::scenarios();        
        
        return [
            
            self::SCENARIO_UPDATE_HEAD_MANAGER => [
                'id_vid_work', 'id_navik', 'name', 'opisanie', 'reyting_start', 
                'id_status_zakaz', 'zametka', 'gorod', 'poselok', 'ulica', 'dom', 
                'kvartira', 'data_start', 'data_end', 'cena'
                ],  
            self::SCENARIO_UPDATE_MANAGER => [
                'id_vid_work', 'id_navik', 'name', 'opisanie', 'reyting_start', 
                'id_status_zakaz', 'zametka', 'gorod', 'poselok', 'ulica', 'dom', 
                'kvartira', 'data_start', 'data_end'
            ],  
            self::SCENARIO_UPDATE_MASTER => [
                'id_status_zakaz'
            ],
            self::SCENARIO_CREATE => [
                'id_vid_work', 'id_navik', 'name', 'cena', 'opisanie', 
                'reyting_start', 'zametka', 'gorod', 'poselok', 'ulica', 'dom', 
                'kvartira', 'data_start', 'data_end', 'dolgota', 'shirota'
            ],
            
            self::SCENARIO_RECOVERY => [
                'id', 'id_vid_work', 'id_navik', 'name', 'cena', 'opisanie', 'reyting_start',
                'zametka', 'gorod', 'poselok', 'ulica', 'dom', 'kvartira', 'id_status_zakaz',
                'id_shag', 'data_registry', 'data_start', 'data_end', 'dolgota', 'shirota',
                'dolgota_change', 'shirota_change', 'image', 'id_region', 'id_ocenka', 'otziv'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№ Заявки',
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
    
    public static function getRelationTablesArray()
    {
        $vid = [];
        $vid['vidNavik'] = VidNavik::find()->indexBy('id')->asArray()->all();        
        $vid['vidStatusZakaz'] = VidStatusZakaz::find()->select(['id', 'name'])->indexBy('id')->asArray()->all();        
        $vid['vidShag'] = VidShag::find()->indexBy('id')->asArray()->all();
        $vid['vidRegion'] = VidRegion::find()->select(['id', 'name'])
                ->where('parent_id <> 0')->indexBy('id')->asArray()->all();
        
        $vid['vidWork'] = VidWork::find()->indexBy('id')->asArray()->all();        
        $vid['vidOcenka'] = VidOcenka::find()->indexBy('id')->asArray()->all();
        
        return $vid;
    }
}
