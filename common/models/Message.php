<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models;

//use Yii;
//use yii\base\Model;

/**
 * Description of Message
 *
 * @author Gradinas
 */
class Message {    
    
    /** 
     * @var int $operation     *  
     */    
    public $operation;
    /** 
     * @var int $status     *  
     */    
    public $status;
    /** 
     * @var str $s_message     *  
     */ 
    public $s_message;
    /** 
     * @var int $s_code     *  
     */ 
    public $s_code;
    /** 
     * @var int $id_chat     *  
     */ 
    public $id_chat;
    /** 
     * @var int $id     *  
     */ 
    public $id;    
    /**
     * @var int $parent_id      *
     */
    public $parent_id;
    /** 
     * @var str $autor     *  
     */     
    public $autor;
    /** 
     * @var int $id_autor     *  
     */     
    public $id_autor;
    /** 
     * @var int $id_user     *  
     */     
    public $id_user;
    /** 
     * @var str $message     *  
     */     
    public $message;
    /** 
     * @var str $date     *  
     */     
    public $date;    
    /** 
     * @var str $time     *  
     */     
    public $time;    
    /** 
     * @var str $file     *  
     */ 
    public $file;   
    
    
    
    /**
     * 
     * @return \common\models\Message
     */  
    public static function create()
    {
        $message = new Message();
        return $message;
    }
    /**
     * 
     * @param array $params - ассоциативный массив
     * @return \common\models\Message
     */
    public static function createOfArray(array $params)
    {
        $message = new Message();
        foreach ($message as $key => $value) {
            if (key_exists($key, $params) && isset($params[$key])) $message->{$key} = $params[$key];
        } 
        return $message;
    }
    /**
     * 
     * @param object $object
     * @return \common\models\Message
     */
    public static function createOfObject($object)
    {
        $message = new Message();
        if (isset($object->attributes)) {
            foreach ($message as $key => $value) {
                if (isset($object->{$key})) $message->{$key} = $object->{$key};
            } 
        } else {        
            foreach ($message as $key => $value) {
                if (property_exists($object, $key) && isset($object->{$key})) $message->{$key} = $object->{$key};
            }  
        }
        return $message;
    }
    
    public function getAttrOfArray()
    {
        $mass = [];
        foreach($this as $key => $value) 
            if ($value != null) $mass[$key] = $value;
        
        return $mass;
    }
    
}
