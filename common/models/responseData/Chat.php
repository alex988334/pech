<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models\responseData;

/**
 * Description of Chat
 *
 * @author Gradinas
 */
class Chat {
    /**
     * @var int $id
     */
    public $id;
    /**
     * @var int $idAuthor
     */
    public $idAuthor;
    /**
     * @var string $alias
     */
    public $alias;
    
    /** 
     * @param array $params - ассоциативный массив
     * @return \common\models\responseData\Chat
     */
    public static function createOfArray(array $params)
    {
        $message = new Chat();
        foreach ($message as $key => $value) {
            if (key_exists($key, $params) && isset($params[$key])) $message->{$key} = $params[$key];
        } 
        return $message;
    }
}
