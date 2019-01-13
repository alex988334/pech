<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\MasterVsZakaz;

/**
 * MasterVsZakazSearch represents the model behind the search form of `common\models\MasterVsZakaz`.
 */
class MasterVsZakazSearch extends MasterVsZakaz
{    
    public $region_name;
    public $username;
    public $imya;
    public $familiya;
    public $name;
    public $opisanie;
    public $vid_work_name;
    public $status_zakaz_name;
    public $shag_name;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_master', 'id_zakaz'], 'integer'],
            [['region_name', 'username', 'imya', 'familiya', 'name', 'opisanie', 
                'vid_work_name', 'status_zakaz_name', 'shag_name'], 'safe'],
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
        if (isset($params['id_zakaz']) && (((string)((int)$params['id_zakaz']))) == $params['id_zakaz']) {
            $this->id_zakaz = $params['id_zakaz'];
        }
        
        $query = MasterVsZakaz::find()
                ->select([
                    'id' => 'master_vs_zakaz.id',
                    'region_name' => 'vid_region.name',
                    'id_master' => 'master_vs_zakaz.id_master',
                    'username',
                    'imya',
                    'familiya',
                    'id_zakaz',
                    'name' => 'zakaz.name',
                    'opisanie',
                    'vid_work_name' => 'vid_work.name',
                    'status_zakaz_name' => 'vid_status_zakaz.name',
                    'shag_name' => 'vid_shag.name'
                ])
                ->where('master.id_region=' . Yii::$app->session->get('id_region'))
                ->joinWith('master')->joinWith('zakaz')->joinWith('user')
                ->joinWith('region')->joinWith('vidWork')
                ->joinWith('statusZakaz')->joinWith('shag')->asArray();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id', 'region_name', 'id_master', 'username', 'imya', 
                'familiya', 'id_zakaz', 'name', 'opisanie', 'vid_work_name',
                'status_zakaz_name', 'shag_name'
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
            'master_vs_zakaz.id_master' => $this->id_master,
            'id_zakaz' => $this->id_zakaz,
        ]);
        
        $query->andFilterWhere(['like', 'vid_region.name', $this->region_name])
                ->andFilterWhere(['like', 'username', $this->username])
                ->andFilterWhere(['like', 'imya', $this->imya])
                ->andFilterWhere(['like', 'familiya', $this->familiya])
                ->andFilterWhere(['like', 'zakaz.name', $this->name])
                ->andFilterWhere(['like', 'opisanie', $this->opisanie])
                 ->andFilterWhere(['like', 'vid_status_zakaz.name', $this->status_zakaz_name])
                 ->andFilterWhere(['like', 'vid_shag.name', $this->shag_name])
                ->andFilterWhere(['like', 'vid_work.name', $this->vid_work_name]);

        return $dataProvider;
    }
}
