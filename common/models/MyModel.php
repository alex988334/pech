<?php

namespace common\models;

use yii\db\ActiveRecord;
use common\models\ManagerTable;


class MyModel extends ActiveRecord
{
    
    
    
    const SCENARIO_MANAGER = 'manager';
    const SCENARIO_HEAD_MANAGER = 'head_manager';
    
    public function scenarios($nameTable)
    {
        $scenarios = parent::scenarios();
        
        $nameTable;
        $scenarios[self::SCENARIO_MANAGER] = ['username', 'password'];
        $scenarios[self::SCENARIO_HEAD_MANAGER] = ['username', 'email', 'password'];
        return $scenarios;
    }
    
}
