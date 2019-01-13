<?php

namespace frontend\models;

use yii\base\Model;

/**
 * Signup form
 */
class MasterZakazForm extends Model
{   
    public $id_zakaz;    

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [            
            [['id_zakaz'], 'required'],
            [['id_zakaz'], 'integer'],
        ];
    }

    public function attributeLabels() {
        return [
            'id_zakaz' => '№ заявки',            
        ];
    }
}
