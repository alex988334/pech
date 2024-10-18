<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace backend\models;

use yii\base\Model;
use common\models\VidRegion;
use common\models\User;

/**
 * Description of ManagerUpdateForm
 *
 * @author Gradinas
 */
class ManagerUpdate extends Manager {
    
    public $id;
    public $id_manager;
    public $familiya;
    public $imya;
    public $otchestvo;
    public $id_region;
    public $phone1;
    public $phone2;
    public $phone3;
    public $username;
    public $email;
    public $status;
    public $user_id;
    public $item_name;                                                          //  название роли
    public $password1;
    public $password2;
    
    
    public function rules()
    {
        return [
            [['username', 'id_manager', 'familiya', 'imya', 'otchestvo', 'id_region', 'phone1'], 'required'],
            [['id_manager', 'id_region', 'updated_at', 'created_at'], 'integer'],
            [['familiya', 'imya', 'otchestvo'], 'string', 'max' => 50],
            [['phone1', 'phone2', 'phone3'], 'string', 'max' => 11],
            [['id', 'username'], 'unique'],           
            [['username', 'email', 'password1', 'password2'], 'string', 'max' => 50],
            [['password_hash'], 'string', 'max' => 255],
            [['imei'], 'string', 'max' => 15],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED, self::STATUS_BLOCKED]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№',
            'id_manager' => '№ менеджера',
            'familiya' => 'Фамилия',
            'imya' => 'Имя',
            'otchestvo' => 'Отчество',
            'id_region' => '№ региона',
            'phone1' => 'Телефон 1',
            'phone2' => 'Телефон 2',
            'phone3' => 'Телефон 3',
            'username' => 'Логин',
            'status' => 'Статус',
            'email' => 'Почта',            
            'item_name' => 'Роль',            
        ];
    }
}
