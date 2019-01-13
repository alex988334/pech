<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;


class UserSearch extends User
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['username'], 'unique'],
            [['username'], 'string', 'max' => 50],
        ];
    }
    
    public function search($params)
    {
        if (isset($params['id']) && (((string)((int)$params['id']))) == $params['id']) {
            $this->id = $params['id'];
        }
        
        $query = User::find()->select(['id', 'username', 'password_hash', 'imei'])->asArray(); 

        $dataProvider = new ActiveDataProvider(['query' => $query]);
        
        $dataProvider->setSort([
            'attributes' => [
                'id', 'username', 'password_hash'              
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) { return $dataProvider; }
        
        $query->andFilterWhere(['id' => $this->id]);

        $query->andFilterWhere(['like', 'username', $this->username]); 
        
        return $dataProvider;
    }    
}
