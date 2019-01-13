<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Chat;

/**
 * ChatSearch represents the model behind the search form of `common\models\Chat`.
 */
class OLDChatSearch extends Chat
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'id_chat', 'id_user', 'parent_id'], 'integer'],
            [['message', 'date', 'time'], 'safe'],
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
        $query = Chat::find()->select([
                'id' => 'chat.id',
                'id_chat',
                'id_user',
                'parent_id',
                'message',
                'date' => 'chat.date',
                'time' => 'chat.time',
                'login' => 'user.username'            
            ])->where('id_chat IN (SELECT id_chat FROM chat WHERE id_user='. Yii::$app->user->getId() .')'
                    /*['id_user' => Yii::$app->user->getId()]*/)
                ->andWhere('id_user<>' . Yii::$app->user->getId())
                    /*->groupBy(['id_chat'])*/->joinWith('users')->asArray();

      //  SELECT * FROM `chat` WHERE id_chat IN (SELECT id_chat FROM chat WHERE id_user=379) AND id_user<>379
        
        // add conditions that should always apply here

        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'id_chat' => $this->id_chat,
            'id_user' => $this->id_user,
            'parent_id' => $this->parent_id,
            'date' => $this->date,
            'time' => $this->time,
        ]);

        $query->andFilterWhere(['like', 'message', $this->message]);

        return $dataProvider;
    }
}
