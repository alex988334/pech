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
    public $item_name;
    
    const ONE_DAY = 86400;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {      
        return [
            [['updated_at', 'created_at', 'id'], 'integer'],
            [['username', 'email', 'imei', 'item_name'], 'string'],
        //    [['created_at', 'updated_at', 'item_name'], 'default', 'value' => null],
           
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED, self::STATUS_BLOCKED]],
        ];
    }
    
    public function search($params)
    {
        if (isset($params['id']) && (((string)((int)$params['id']))) == $params['id']) {
            $this->id = $params['id'];
        }
        if ($id = Yii::$app->request->post('id')) {
            $this->id = $id;
        }
        
        
        $query = User::find()->select(['id', 'username', 'password_hash', 'email', 
                        'imei', 'status', 'user.created_at', 'user.updated_at'])
                ->joinWith('role')->asArray(); 
        
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        
        $dataProvider->setSort([
            'attributes' => [
                'id', 'username', 'password_hash', 'imei', 'email', 'status',
                'created_at', 'updated_at', 'item_name'             
            ]
        ]);
        $this->load($params);

        if (isset($params['UserSearch']['created_at']) && (!empty($params['UserSearch']['created_at']))) {
            $this->created_at = date('U', strtotime($params['UserSearch']['created_at']));
        }
        if (isset($params['UserSearch']['updated_at']) && (!empty($params['UserSearch']['updated_at']))) {
            $this->updated_at = date('U', strtotime($params['UserSearch']['updated_at']));
        }
                
        if (!$this->validate()) { 
            Yii::debug($this->errors);
            return $dataProvider; 
        }        

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status, 
            'auth_assignment.item_name' => $this->item_name,            
        ]);
        
        
        if (!empty($this->created_at)) {
            $query->andFilterWhere(['between', 'user.created_at', $this->created_at, $this->created_at + self::ONE_DAY]);
        }
        if (!empty($this->updated_at)) {
            $query->andFilterWhere(['between', 'user.updated_at', $this->updated_at, $this->updated_at + self::ONE_DAY]);
        }
        $query->andFilterWhere(['like', 'username', $this->username])
                ->andFilterWhere(['like', 'email', $this->email])       
                ->andFilterWhere(['like', 'imei', $this->imei]);        
        
        return $dataProvider;
    }    
}
