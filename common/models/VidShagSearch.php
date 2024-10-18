<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models;

use yii\data\ActiveDataProvider;

/**
 * Description of VidRegionSearch
 *
 * @author Gradinas
 */
class VidShagSearch extends VidShag {
    
    public $id;    
    public $name;
    public $sort;   


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [       
            [['id', 'sort'], 'integer'],
            [['name'], 'string', 'max' => 50],
        ];
    }
    
    public function search($params)
    {
        $query = VidShag::find();
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [ 'id', 'name', 'sort' ]
        ]);
        
        $this->load($params);

        if (!$this->validate()) {          
            return $dataProvider;
        }
        
        $query->andFilterWhere([
            'id' => $this->id,
            'sort' => $this->sort          
        ]);
        
        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}