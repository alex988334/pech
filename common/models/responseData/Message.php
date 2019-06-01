<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models\responseData;

//use Yii;
//use yii\base\Model;

/**
 * Description of Message
 *
 * @author Gradinas
 */
class Message {    
    
    /** 
     * @var int $id     *  
     */ 
    public $id; 
    /** 
     * @var int $id_chat     *  
     */ 
    public $idChat;
    /**
     * @var int $parent_id      *
     */
    public $parentId;
    /** 
     * @var str $autor     *  
     */     
    public $autor;
    /** 
     * @var int $id_autor     *  
     */     
    public $idAuthor;
    /** 
     * @var str $message     *  
     */     
    public $message;
    /** 
     * @var int $m_status     *  
     */ 
    public $mStatus;
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
