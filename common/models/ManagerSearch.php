<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Manager;
use common\models\User;

/**
 * ManagerSearch represents the model behind the search form of `common\models\Manager`.
 */
class ManagerSearch extends Manager
{    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_manager', 'id_region'], 'integer'],
           // [['familiya', 'imya', 'otchestvo', 'phone1', 'phone2', 'phone3'], 'safe'],
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
        $role = Yii::$app->session->get('role');
            
        if ($role == User::HEAD_MANAGER) {
            $query = Manager::find()->with('user')->with('region')->asArray();
        } else {
            $query = Manager::find()->where(['id_manager' => Yii::$app->user->getId()])
                    ->with('user')->with('region')->limit(1)->asArray();
        }    

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $dataProvider->setSort(false);


        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
     /*   $query->andFilterWhere([
            'id' => $this->id,
            'id_manager' => $this->id_manager,
            'id_region' => $this->id_region,
        
        
        ]);*/

     /*   $query->andFilterWhere(['like', 'familiya', $this->familiya])
            ->andFilterWhere(['like', 'imya', $this->imya])
            ->andFilterWhere(['like', 'otchestvo', $this->otchestvo])
            ->andFilterWhere(['like', 'phone1', $this->phone1])
            ->andFilterWhere(['like', 'phone2', $this->phone2])
            ->andFilterWhere(['like', 'phone3', $this->phone3]);
*/
        return $dataProvider;
    }
}
