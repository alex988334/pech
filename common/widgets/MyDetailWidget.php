<?php

namespace common\widgets;
use Yii;
use yii\base\Widget;



class MyDetailWidget extends Widget
{
    /**
     *
     * @var type Array
     * Переменная хранит модель (обязательно в форме массива)     * 
     */
    public $model;    
    /**
     *
     * @var type Array
     * Переменная хранит массив названий полей, которые необходимо подменить
     * значениями из массива $model, 
     * по связке nameFields['id_name_attr'] => (['name_attr' => ['id', 'name']])
     */
    public $nameFields;    
    public $attributes;
    private $error = false;
    private $many = false;
    
    public function init() {
        parent::init();
        if ($this->model === null) {
            $this->error = true;
            return;
        }
        
        if (is_array($this->model[0])) $this->many = true;        
        if ($this->attributes === null) 
            if ($this->many) $massAttr = array_keys($this->model[0]);   
            else $massAttr = array_keys($this->model);
        
        if (isset($massAttr)) $this->attributes = $massAttr;         
        if ($this->nameFields !== null) {
           // $massAttr = [];
            foreach ($this->nameFields as $value)  {              
                if ($this->many) {
                    if (!array_key_exists($value, $this->attributes))
                    $massAttr[] = $value; 
                }
            }  
            $this->attributes = $massAttr;
        }                     
    }  
        
    
    public function run() {
        //parent::run();
        if ($this->error) return;
        if ($this->many){
            foreach ($model as $one)
                foreach ($this->attributes as $attr){
                    
                }
        }
        
        
        
        return ;
    }
    
}