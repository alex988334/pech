<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\HistoryZakaz;

/**
 * HistoryZakazSearch represents the model behind the search form of `common\models\HistoryZakaz`.
 */
class HistoryZakazSearch extends HistoryZakaz
{
    public $status_history_name;    
    public $vid_work_name;    
    public $navik_name;
    public $status_zakaz_name;
    public $shag_name;
    public $region_name;
    public $ocenka_name;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_status_history', 'id_user', 'id_zakaz', 'id_vid_work', 'id_navik', 'cena', 'reyting_start', 'dom', 'kvartira', 'id_status_zakaz', 'id_shag', 'id_region', 'id_ocenka'], 'integer'],
            [['date', 'time', 'role', 'username', 'name', 'opisanie', 'zametka', 'gorod', 'poselok', 'ulica', 'data_registry', 'data_start', 'data_end', 'image', 'otziv'], 'safe'],
            [['dolgota', 'shirota', 'dolgota_change', 'shirota_change'], 'number'],
            
            [['vid_work_name', 'navik_name', 'status_zakaz_name', 'shag_name', 'region_name', 'ocenka_name', 'status_history_name'], 'safe'], 
            
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
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        if (isset($params['id']) && (((string)((int)$params['id']))) == $params['id']) {
            $this->id = $params['id'];
        }
        
        $query = HistoryZakaz::find()
                ->select([
                    'id' => 'history_zakaz.id', 'date', 'time', 'id_status_history', 'role', 
                    'username', 'id_user', 'status_history_name' => 'vid_status_history.name',
                    'id_zakaz', 'id_vid_work', 'vid_work_name' => 'vid_work.name',
                    'id_navik', 'navik_name' => 'vid_navik.name', 'history_zakaz.name',
                    'cena', 'opisanie', 'reyting_start', 'zametka', 'gorod',
                    'poselok', 'ulica', 'dom', 'kvartira', 'id_status_zakaz',
                    'status_zakaz_name' => 'vid_status_zakaz.name',
                    'id_shag', 'shag_name' => 'vid_shag.name', 'data_registry',
                    'data_start', 'data_end', 'dolgota' => 'history_zakaz.dolgota',
                    'shirota' => 'history_zakaz.shirota', 'dolgota_change', 'shirota_change',
                    'image', 'id_region', 'region_name' => 'vid_region.name',
                    'id_ocenka', 'ocenka_name' => 'vid_ocenka.name', 'otziv'
                ])     
                ->joinWith('statusHistory')
                ->joinWith('vidWork')->joinWith('navik')->joinWith('shag')
                ->joinWith('region')->joinWith('statusZakaz')->joinWith('ocenka')
               // ->where(['id_region' => Yii::$app->session->get('id_region')])
                ->asArray();  

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $dataProvider->setSort([
            'attributes' => [
                'id', 'date', 'time', 'id_status_history', 'status_history_name', 'role', 'username', 
                'id_user', 'id_zakaz', 'id_vid_work', 'vid_work_name', 'id_navik', 'navik_name',
                'name', 'cena', 'opisanie', 'reyting_start', 'zametka',
                'gorod', 'poselok', 'ulica', 'dom', 'kvartira', 'id_status_zakaz', 
                'status_zakaz_name', 'id_shag', 'shag_name','data_registry',
                'data_start', 'data_end', 'dolgota', 'shirota', 'dolgota_change',
                'shirota_change', 'image', 'id_region', 'region_name', 'id_ocenka',
                'ocenka_name', 'otziv'                  
            ]
        ]);    

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'date' => $this->date,
            'time' => $this->time,
            'id_status_history' => $this->id_status_history,
            'id_user' => $this->id_user,
            'id_zakaz' => $this->id_zakaz,
            'id_vid_work' => $this->id_vid_work,
            'id_navik' => $this->id_navik,
            'cena' => $this->cena,
            'reyting_start' => $this->reyting_start,
            'dom' => $this->dom,
            'kvartira' => $this->kvartira,
            'id_status_zakaz' => $this->id_status_zakaz,
            'id_shag' => $this->id_shag,
            'data_registry' => $this->data_registry,
            'data_start' => $this->data_start,
            'data_end' => $this->data_end,
            'dolgota' => $this->dolgota,
            'shirota' => $this->shirota,
            'dolgota_change' => $this->dolgota_change,
            'shirota_change' => $this->shirota_change,
            'id_region' => $this->id_region,
            'id_ocenka' => $this->id_ocenka,
        ]);

        $query->andFilterWhere(['like', 'role', $this->role])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'opisanie', $this->opisanie])
            ->andFilterWhere(['like', 'zametka', $this->zametka])
            ->andFilterWhere(['like', 'gorod', $this->gorod])
            ->andFilterWhere(['like', 'poselok', $this->poselok])
            ->andFilterWhere(['like', 'ulica', $this->ulica])
            ->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'otziv', $this->otziv])
                ->andFilterWhere(['like', 'vid_status_history.name', $this->status_history_name])
                ->andFilterWhere(['like', 'vid_work.name', $this->vid_work_name])
                ->andFilterWhere(['like', 'vid_navik.name', $this->navik_name])
                ->andFilterWhere(['like', 'vid_status_zakaz.name', $this->status_zakaz_name])
                ->andFilterWhere(['like', 'vid_shag.name', $this->shag_name])
                ->andFilterWhere(['like', 'vid_ocenka.name', $this->ocenka_name])
                ->andFilterWhere(['like', 'vid_region.name', $this->region_name]);

        return $dataProvider;
    }
}
