<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\HistoryKlient;

/**
 * HistoryKlientSearch represents the model behind the search form of `common\models\HistoryKlient`.
 */
class HistoryKlientSearch extends HistoryKlient
{
    public $status_history_name;
    public $status_on_off_name;    
    public $region_name;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_status_history', 'id_user', 'id_klient', 'vozrast', 
                'id_status_on_off', 'reyting', 'balans', 'id_region', 'old_id'], 'integer'],
            [['date', 'time', 'role', 'username', 'imya', 'familiya', 
                'otchestvo', 'phone', 'status_history_name', 'status_on_off_name', 'region_name'], 'safe'],
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
        $query = HistoryKlient::find()->select([
                'id' => 'history_klient.id', 'date', 'time', 'id_status_history', 
                'status_history_name' => 'vid_status_history.name', 'role', 
                'username', 'id_user', 'id_klient', 'imya', 'familiya', 'otchestvo', 
                'vozrast', 'id_status_on_off', 'status_on_off_name' => 'vid_default.name',
                'phone', 'reyting', 'balans', 'id_region', 'region_name' => 'vid_region.name'
            ])
            ->joinWith('statusHistory')->joinWith('region')
            ->joinWith('statusOnOff')->asArray();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'date', 'time', 'id_status_history', 'role', 'username', 'id_user', 
                'id', 'id_klient', 'imya', 'familiya', 'otchestvo', 'vozrast', 
                'status_on_off_name', 'phone', 'reyting', 'balans', 'region_name',
                'status_history_name'
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
            'history_klient.id' => $this->id,
            'date' => $this->date,
            'time' => $this->time,
            'id_status_history' => $this->id_status_history,
            'id_user' => $this->id_user,
            'id_klient' => $this->id_klient,
            'vozrast' => $this->vozrast,
            'id_status_on_off' => $this->id_status_on_off,
            'reyting' => $this->reyting,
            'balans' => $this->balans,
            'id_region' => $this->id_region,
            'old_id' => $this->old_id,
        ]);

        $query->andFilterWhere(['like', 'role', $this->role])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'imya', $this->imya])
            ->andFilterWhere(['like', 'familiya', $this->familiya])
            ->andFilterWhere(['like', 'otchestvo', $this->otchestvo])
            ->andFilterWhere(['like', 'phone', $this->phone])
                ->andFilterWhere(['like', 'vid_status_history.name', $this->status_history_name])
                ->andFilterWhere(['like', 'vid_default.name', $this->status_on_off_name])
                ->andFilterWhere(['like', 'vid_region.name', $this->region_name]);

        return $dataProvider;
    }
}
