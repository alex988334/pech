<?php

namespace backend\models;

use Yii;
use yii\data\ActiveDataProvider;
use backend\models\Manager;

/**
 * Description of ManagerSearch
 *
 * @author Gradinas
 */
class ManagerSearch extends Manager {
    
    public $id_manager;    
    public $username;
    public $email;
    public $status;
    public $familiya;
    public $imya;
    public $otchestvo;
    public $item_name;                                                          //  название роли
    public $name;                                                               //  название региона
    public $phone1;
    public $phone2;
    public $phone3;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
         //   [['name', 'parent_id', 'dolgota', 'shirota'], 'required'],
            [['id', 'id_manager', 'status'], 'integer'],            
            [['username', 'name', 'familiya', 'imya', 'otchestvo', 
                    'email', 'item_name'], 'string', 'max' => 50],            
            [['phone1', 'phone2', 'phone3'], 'string', 'max' => 12],
        ];
    }
    
    public function search($params)
    {        
        $query = Manager::find()->select(['manager.id', 'username', 'id_manager', 'familiya',
            'imya', 'otchestvo', 'id_region', 'name', 'status', 'email', 'item_name',
            'phone1', 'phone2', 'phone3'])->joinWith('role')->joinWith('user')->joinWith('region');
        
            //  создаем провайдера данных
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
            //  добавляем сортировку
        $dataProvider->setSort([
            'attributes' => [ 'username', 'id_manager', 'familiya', 
            'imya', 'otchestvo', 'name', 'email',
            'phone1', 'phone2', 'phone3', 'status', 'item_name',
                /*=> [
                'asc' => ['auth_assignment.item_name' => SORT_ASC],
                'desc' => ['auth_assignment.item_name' => SORT_DESC],              
                'default' => SORT_ASC
            ] */]
        ]);
            //  грузим параметры в модель
        $this->load($params);

        if (!$this->validate()) { 
            Yii::debug('Проверка не пройдена');
            return $dataProvider;
        }
            //  добавляем условия к запросу 
        $query->andFilterWhere([
            'id_manager' => $this->id_manager,    
            'name' => $this->name,
            'status' => $this->status,
            'auth_assignment.item_name' =>$this->item_name
        ]);
        
        $query->andFilterWhere(['like', 'user.username', $this->username])
                ->andFilterWhere(['like', 'phone1', $this->phone1])
                ->andFilterWhere(['like', 'phone2', $this->phone2])
                ->andFilterWhere(['like', 'phone3', $this->phone3])
                ->andFilterWhere(['like', 'familiya', $this->familiya])
                ->andFilterWhere(['like', 'imya', $this->imya])
                ->andFilterWhere(['like', 'otchestvo', $this->otchestvo])
                ->andFilterWhere(['like', 'user.email', $this->email])
               // ->andFilterWhere(['like', 'auth_assignment.item_name', $this->item_name])
               ;
                
                

        return $dataProvider;
    }
    
}
