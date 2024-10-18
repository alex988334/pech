<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "history_entry".
 *
 * @property string $id №
 * @property string $id_user № пользователя
 * @property int $datetime Дата
 */
class HistoryEntrySearch extends HistoryEntry
{      
    public $item_name;
    public $username;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [            
            [['date', 'time'], 'safe'],
            [['id', 'id_user'], 'integer'],
            [['ip', 'action'], 'string', 'max' => 20],
            [['item_name', 'username'], 'string', 'max' => 64],
        ];
    }

    public function search($params)
    {
        $query = HistoryEntry::find()
                ->joinWith('user')->joinWith('role')->asArray();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'date', 'time', 'id', 'action', 'ip', 'item_name', 'username', 'id_user',                
            ],
            'defaultOrder' => ['id' => SORT_DESC]
        ]);
        
        $this->load($params);

        if (!$this->validate()) {          
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'history_entry.id' => $this->id,
            'action' => $this->action,
            'id_user' => $this->id_user,
            'date' => $this->date, 
            'time' => $this->time,  
            'auth_assignment.item_name' => $this->item_name,
        ]);

        $query->andFilterWhere(['like', 'user.username', $this->username])
            ->andFilterWhere(['like', 'ip', $this->ip]);

        return $dataProvider;
    }
}
