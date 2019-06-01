<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models\responseData;

/**
 * Description of User
 *
 * @author Gradinas
 */
class User {
    /**
     * @var int $id
     */
    public $id;
    /**
     * @var string $login
     */
    public $login;
    /**
     * @var string $fio
     */
    public $fio;
    
    /** 
     * @param array $params - ассоциативный массив
     * @return \common\models\responseData\User
     */
    public static function createOfArray(array $params)
    {
        $message = new User();
        foreach ($message as $key => $value) {
            if (key_exists($key, $params) && isset($params[$key])) $message->{$key} = $params[$key];
        } 
        return $message;
    }
}
