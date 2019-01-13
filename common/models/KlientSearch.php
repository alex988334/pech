<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Klient;

/**
 * KlientSearch represents the model behind the search form of `common\models\Klient`.
 */
class KlientSearch extends Klient
{
    
    public $status_on_off_name;    
    public $region_name;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_klient', 'vozrast', 'id_status_on_off', 'reyting', 'balans', 'id_region', /* 'old_id'*/ ], 'integer'],
            [['imya', 'familiya', 'otchestvo', 'phone', 'status_on_off_name', 'region_name'], 'safe'],
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
      //  if (Yii::$app->session->get('role') == 'manager' || Yii::$app->session->get('role') == 'head_manager') {
            $query = Klient::find()
                    ->select([
                        'id' => 'klient.id', 'id_klient', 'imya', 'familiya', 'otchestvo', 
                        'vozrast', 'id_status_on_off', 'status_on_off_name' => 'vid_default.name',
                        'phone', 'reyting', 'balans', 'id_region', 'region_name' => 'vid_region.name'
                    ])
                    ->where(['id_region' => Yii::$app->session->get('id_region')])
                    ->joinWith('region')
                    ->joinWith('statusOnOff')
                    ->asArray();
   /*     } /*elseif (Yii::$app->session->get('role') == 'head_manager') {
            $query = Klient::find()
                    ->select([
                        'id' => 'klient.id', 'id_klient', 'imya', 'familiya', 'otchestvo', 
                        'vozrast', 'id_status_on_off', 'status_on_off_name' => 'vid_default.name',
                        'phone', 'reyting', 'balans', 'id_region', 'region_name' => 'vid_region.name'
                    ])
                    ->joinWith('region')
                    ->joinWith('statusOnOff')
                    ->asArray();
        }*/

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $dataProvider->setSort([
            'attributes' => [
                'id', 'id_klient', 'imya', 'familiya', 'otchestvo', 'vozrast', 
                'status_on_off_name', 'phone', 'reyting', 'balans', 'region_name'
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
            'klient.id' => $this->id,
            'id_klient' => $this->id_klient,
            'vozrast' => $this->vozrast,
            'id_status_on_off' => $this->id_status_on_off,
            'reyting' => $this->reyting,
            'balans' => $this->balans,
            'id_region' => $this->id_region,
            'old_id' => $this->old_id,
        ]);

        $query->andFilterWhere(['like', 'imya', $this->imya])
            ->andFilterWhere(['like', 'familiya', $this->familiya])
            ->andFilterWhere(['like', 'otchestvo', $this->otchestvo])
            ->andFilterWhere(['like', 'phone', $this->phone])
                ->andFilterWhere(['like', 'vid_default.name', $this->status_on_off_name])
                ->andFilterWhere(['like', 'vid_region.name', $this->region_name]);

        return $dataProvider;
    }
}
