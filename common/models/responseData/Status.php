<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models\responseData;

/**
 * Description of Status
 *
 * @author Gradinas
 */
class Status {
    /**   
     * @var int $status
     */
    public $status;
    /**
     * @var int $operation
     */
    public $operation;
    /**
     * @var string $sMessage
     */
    public $sMessage;
    /**
     * @var int $sCode
     */
    public $sCode;
    
    /** 
     * @param array $params - ассоциативный массив
     * @return \common\models\responseData\Status
     */
    public static function createOfArray(array $params)
    {
        $message = new Status();
        foreach ($message as $key => $value) {
            if (key_exists($key, $params) && isset($params[$key])) $message->{$key} = $params[$key];
        } 
        return $message;
    }
}
