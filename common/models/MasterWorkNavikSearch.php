<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\MasterWorkNavik;
use common\models\AuthItem;

/**
 * MasterWorkNavikSearch represents the model behind the search form of `common\models\MasterWorkNavik`.
 */
class MasterWorkNavikSearch extends MasterWorkNavik
{
    
    public $work_name;
    public $navik_name;
    public $navik_sort;
    public $imya;
    public $familiya;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_master', 'id_vid_work', 'id_vid_navik'], 'integer'],
            [['work_name', 'navik_name', 'imya', 'familiya', 'navik_sort'], 'safe']
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
        
        $role = Yii::$app->session->get('role');
        if ($role == AuthItem::MASTER) {
            $condition = 'master.id_master=' . Yii::$app->user->getId();
        } elseif ($role == AuthItem::HEAD_MANAGER || $role == AuthItem::MANAGER) {
            $condition = 'id_region=' . Yii::$app->session->get('id_region');
        } else {
            Yii::$app->user->logout();
        }
        $query = MasterWorkNavik::find()
                ->select([
                    'id' => 'master_work_navik.id',
                    'id_master' => 'master_work_navik.id_master',
                    'imya',
                    'familiya',
                    'id_vid_work',
                    'id_vid_navik',
                    'work_name' => 'vid_work.name',
                    'navik_sort' => 'vid_navik.sort',
                    'navik_name' => 'vid_navik.name'
                ])
                ->where($condition)
                ->joinWith('master')
                ->joinWith('vidWork')
                ->joinWith('vidNavik')
                ->asArray();
            

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $dataProvider->setSort([
            'attributes' => [
                'id', 'id_master', 'imya', 'familiya', /*'id_vid_work',*/ 'work_name',
                'id_vid_navik', 'navik_name', 'navik_sort'            
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
            'master_work_navik.id' => $this->id,
            'master_work_navik.id_master' => $this->id_master,
         //   'id_vid_work' => $this->id_vid_work,
       //     'id_vid_navik' => $this->id_vid_navik,
        ]);
        
        $query->andFilterWhere(['like', 'master.imya', $this->imya])
            ->andFilterWhere(['like', 'master.familiya', $this->familiya])
            ->andFilterWhere(['like', 'vid_work.name', $this->work_name])
            ->andFilterWhere(['like', 'vid_navik.name', $this->navik_name])
            ->andFilterWhere(['like', 'vid_navik.sort', $this->navik_sort]);

        return $dataProvider;
    }
}
