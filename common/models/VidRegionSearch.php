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
class VidRegionSearch extends VidRegion {
    
    public $id;    
    public $name;
    public $parent_id;
    public $dolgota;
    public $shirota;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
         //   [['name', 'parent_id', 'dolgota', 'shirota'], 'required'],
            [['parent_id', 'id'], 'integer'],
            [['dolgota', 'shirota'], 'number'],
            [['name'], 'string', 'max' => 50],
        ];
    }
    
    public function search($params)
    {
        $query = VidRegion::find();
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [ 'id', 'name', 'parent_id', 'dolgota', 'shirota' ]
        ]);
        
        $this->load($params);

        if (!$this->validate()) {          
            return $dataProvider;
        }
        
        $query->andFilterWhere([
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'dolgota' => $this->dolgota,
            'shirota' => $this->shirota,
        ]);
        
        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
