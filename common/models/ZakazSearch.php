<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Zakaz;
use common\models\VidStatusZakaz;


/**
 * ZakaziSearch represents the model behind the search form of `common\models\Zakazi`.
 */
class ZakazSearch extends Zakaz
{    
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
            [['id', 'id_vid_work', 'id_navik', 'cena', 'reyting_start', 'dom', 'kvartira', 'id_status_zakaz', 'id_shag', 'id_region', 'id_ocenka'], 'integer'],
            [['name', 'opisanie', 'zametka', 'gorod', 'poselok', 'ulica', 'data_registry', 'data_start', 'data_end', 'image', 'otziv'], 'safe'],
            [['dolgota', 'shirota', 'dolgota_change', 'shirota_change'], 'number'],
            [['vid_work_name', 'navik_name', 'status_zakaz_name', 'shag_name', 'region_name', 'ocenka_name'], 'safe'],            
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
        
        $session = Yii::$app->session;
        $role = $session->get('role');
        if ($role == 'manager' || $role == 'head_manager') {
            
            $query = Zakaz::find()
                    ->select([
                        'id' => 'zakaz.id', 'id_vid_work', 'vid_work_name' => 'vid_work.name',
                        'id_navik', 'navik_name' => 'vid_navik.name', 'zakaz.name',
                        'cena', 'opisanie', 'reyting_start', 'zametka', 'gorod',
                        'poselok', 'ulica', 'dom', 'kvartira', 'id_status_zakaz',
                        'status_zakaz_name' => 'vid_status_zakaz.name',
                        'id_shag', 'shag_name' => 'vid_shag.name', 'data_registry',
                        'data_start', 'data_end', 'dolgota' => 'zakaz.dolgota',
                        'shirota' => 'zakaz.shirota', 'dolgota_change', 'shirota_change',
                        'image', 'id_region', 'region_name' => 'vid_region.name',
                        'id_ocenka', 'ocenka_name' => 'vid_ocenka.name', 'otziv'
                    ])                    
                    ->joinWith('vidWork')->joinWith('navik')->joinWith('shag')
                    ->joinWith('region')->joinWith('statusZakaz')->joinWith('ocenka')
                    ->where(['id_region' => $session->get('id_region')]);
            
            if (Yii::$app->session->get('invisibleExecutedOrders') == FALSE) {
                $query->andWhere(['<>', 'id_status_zakaz', VidStatusZakaz::ORDER_EXECUTED]);
                $query->andWhere(['<>', 'id_status_zakaz', VidStatusZakaz::ORDER_CANCELLED]);
            } 
            if (Yii::$app->session->get('invisibleBlockedOrders') == FALSE) {
                $query->andWhere(['<>', 'id_status_zakaz', VidStatusZakaz::ORDER_UNAVAILABLE]);            
            } 
            
            $query->asArray();
            
        } elseif($role == 'master') {   
            
            $selectedVidWork = $params['id_vid_work'] ?? 1; 
            if (!VidWork::find()->where('id=:id', [':id' => $selectedVidWork])->limit(1)->one()) {                
                $selectedVidWork = 1; 
                $session->setFlash('message', 'Параметр ошибочен');                
            }
            
            $session->set('selectedVidWork', $selectedVidWork);
            $selectedRegion = $session->get('selectedRegion') 
                    ??  $session->get('id_region');   
            
            
            $grantStatus = VidStatusZakaz::find()->select(['id'])
                    ->where(['visibility_master' => 1])->asArray()->all(); 
            foreach ($grantStatus as $one) { $id[] = $one['id']; }
            $id = implode(', ', $id);
            
            $query = Zakaz::find()
                    ->select([
                        'id' => 'zakaz.id', 'id_navik', 'id_vid_work',
                        'navik_name' => 'vid_navik.name', 'zakaz.name', 'cena',
                        'reyting_start', 'gorod', 'poselok', 'ulica', 'id_status_zakaz',
                        'status_zakaz_name' => 'vid_status_zakaz.name', 
                        'data_registry', 'data_end', 'id_region'                                    
                    ])
                    ->joinWith('navik')              
                    ->joinWith('statusZakaz')
                    ->where(['id_vid_work' => $selectedVidWork]) 
                    ->andWhere(['id_region' => $selectedRegion])
                    ->andWhere( 'zakaz.id_status_zakaz IN ('. $id .')')                    
                    ->asArray();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $dataProvider->setSort([
            'attributes' => [
                'id', 'id_vid_work', 'vid_work_name', 'id_navik', 'navik_name',
                'name', 'cena', 'opisanie', 'reyting_start', 'zametka',
                'gorod', 'poselok', 'ulica', 'dom', 'kvartira', 'id_status_zakaz', 
                'status_zakaz_name', 'id_shag', 'shag_name','data_registry',
                'data_start', 'data_end', 'dolgota', 'shirota', 'dolgota_change',
                'shirota_change', 'image', 'id_region', 'region_name', 'id_ocenka',
                'ocenka_name', 'otziv'                  
            ]
        ]);

        $this->load($params);

        if (isset($this->data_registry) && (!empty($this->data_registry))) {
            $this->data_registry = date('Y-m-d', strtotime($this->data_registry));
        }
        if (isset($this->data_start) && (!empty($this->data_start))) {
            $this->data_start = date('Y-m-d', strtotime($this->data_start));
        }
        if (isset($this->data_end) && (!empty($this->data_end))) {
            $this->data_end = date('Y-m-d', strtotime($this->data_end));
        }
        
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            
            'zakaz.id' => $this->id,
            'id_vid_work' => $this->id_vid_work,
            'id_navik' => $this->navik,
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
        ]);

        $query->andFilterWhere(['like', 'zakaz.name', $this->name])
            ->andFilterWhere(['like', 'opisanie', $this->opisanie])
            ->andFilterWhere(['like', 'zametka', $this->zametka])
            ->andFilterWhere(['like', 'gorod', $this->gorod])
            ->andFilterWhere(['like', 'poselok', $this->poselok])
            ->andFilterWhere(['like', 'ulica', $this->ulica])
            ->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'otziv', $this->otziv])
                ->andFilterWhere(['like', 'vid_work.name', $this->vid_work_name])
                ->andFilterWhere(['like', 'vid_navik.name', $this->navik_name])
                ->andFilterWhere(['like', 'vid_status_zakaz.name', $this->status_zakaz_name])
                ->andFilterWhere(['like', 'vid_shag.name', $this->shag_name])
                ->andFilterWhere(['like', 'vid_ocenka.name', $this->ocenka_name])
                ->andFilterWhere(['like', 'vid_region.name', $this->region_name]);

        return $dataProvider;
    }
}
