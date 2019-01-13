<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Master;

/**
 * MasterSearch represents the model behind the search form of `common\models\Master`.
 */
class MasterSearch extends Master
{
    public $status_on_off_name;    
    public $region_name;
    public $status_work_name;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_master', 'id_status_on_off', 'vozrast', 'staj', 'reyting', 'id_status_work', 'balans', 'id_region', 'limit_zakaz'], 'integer'],
            [['familiya', 'imya', 'otchestvo', 'data_registry', 'data_unregistry', 'phone', 'mesto_jitelstva', 'mesto_raboti'], 'safe'],
            [['status_on_off_name', 'region_name', 'status_work_name'], 'safe']
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
        if (isset($params['id_master']) && (((string)((int)$params['id_master']))) == $params['id_master']) {
            $this->id_master = $params['id_master'];
        }
        
        $session = Yii::$app->session;
        
        $query = Master::find()
            ->select([
                'id' => 'master.id', 'id_master', 'familiya', 'imya', 'otchestvo',
                'id_status_on_off', 'status_on_off_name' => 'vid_default.name',  
                'vozrast', 'staj', 'reyting', 'id_status_work',
                'status_work_name' => 'vid_status_work.name', 'data_registry',
                'data_unregistry', 'phone', 'mesto_jitelstva', 'mesto_raboti',
                'balans', 'id_region', 'region_name' => 'vid_region.name', 'limit_zakaz',
            ])                    
            ->joinWith('region')->joinWith('statusOnOff')
            ->joinWith('statusWork')
            ->where(['id_region' => $session->get('id_region')])
            ->asArray();            
        
        
// 'izdelie_navik_name', 
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $dataProvider->setSort([
            'attributes' => [
                'id', 'id_master', 'familiya', 'imya', 'otchestvo',
                'status_on_off_name', 'vozrast', 'staj', 'reyting',
                'status_work_name', 'data_registry', 'data_unregistry',
                'phone', 'mesto_jitelstva', 'mesto_raboti', 'balans',
                'region_name', 'limit_zakaz'              
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
            'id_master' => $this->id_master,
            'id_status_on_off' => $this->id_status_on_off,
            'vozrast' => $this->vozrast,
            'staj' => $this->staj,
            'reyting' => $this->reyting,
            'id_status_work' => $this->id_status_work,
            'data_registry' => $this->data_registry,
            'data_unregistry' => $this->data_unregistry,
            'balans' => $this->balans,
            'id_region' => $this->id_region,
            'limit_zakaz' => $this->limit_zakaz,
        ]);

        $query->andFilterWhere(['like', 'familiya', $this->familiya])
            ->andFilterWhere(['like', 'imya', $this->imya])
            ->andFilterWhere(['like', 'otchestvo', $this->otchestvo])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'mesto_jitelstva', $this->mesto_jitelstva])
            ->andFilterWhere(['like', 'mesto_raboti', $this->mesto_raboti])
                ->andFilterWhere(['like', 'vid_default.name', $this->status_on_off_name])
                ->andFilterWhere(['like', 'vid_status_work.name', $this->status_work_name])
                ->andFilterWhere(['like', 'vid_region.name', $this->region_name]);
        
        

        return $dataProvider;
    }
}
