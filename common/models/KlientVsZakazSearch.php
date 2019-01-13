<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\KlientVsZakaz;

/**
 * KlientVsZakazSearch represents the model behind the search form of `common\models\KlientVsZakaz`.
 */
class KlientVsZakazSearch extends KlientVsZakaz
{    
    public $region_name;
    public $username;
    public $imya;
    public $familiya;
    public $name;
    public $opisanie;
    public $vid_work_name;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_klient', 'id_zakaz', ], 'integer'],
            [['region_name', 'username', 'imya', 'familiya', 'name', 'opisanie', 'vid_work_name'], 'safe'],
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
        if (isset($params['id_klient']) && (((string)((int)$params['id_klient']))) == $params['id_klient']) {
            $this->id_klient = $params['id_klient'];
        }
        if (isset($params['id_zakaz']) && (((string)((int)$params['id_zakaz']))) == $params['id_zakaz']) {
            $this->id_zakaz = $params['id_zakaz'];
        }
        
        $query = KlientVsZakaz::find()
                ->select([
                    'id' => 'klient_vs_zakaz.id',
                    'region_name' => 'vid_region.name',
                    'id_klient' => 'klient_vs_zakaz.id_klient',
                    'username',
                    'imya',
                    'familiya',
                    'id_zakaz',
                    'name' => 'zakaz.name',
                    'opisanie',
                    'vid_work_name' => 'vid_work.name',
                ])
                ->where('klient.id_region=' . Yii::$app->session->get('id_region'))
                ->joinWith('klient')->joinWith('zakaz')->joinWith('user')
                ->joinWith('region')->joinWith('vidWork')->asArray();
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $dataProvider->setSort([
            'attributes' => [
                'id', 'region_name', 'id_klient', 'username', 'imya', 
                'familiya', 'id_zakaz', 'name', 'opisanie', 'vid_work_name'
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
            'klient_vs_zakaz.id' => $this->id,
            'klient_vs_zakaz.id_klient' => $this->id_klient,
            'id_zakaz' => $this->id_zakaz,
        ]);
        
        $query->andFilterWhere(['like', 'vid_region.name', $this->region_name])
                ->andFilterWhere(['like', 'username', $this->username])
                ->andFilterWhere(['like', 'imya', $this->imya])
                ->andFilterWhere(['like', 'familiya', $this->familiya])
                ->andFilterWhere(['like', 'zakaz.name', $this->name])
                ->andFilterWhere(['like', 'opisanie', $this->opisanie])
                ->andFilterWhere(['like', 'vid_work.name', $this->vid_work_name]);

        return $dataProvider;
    }
}
