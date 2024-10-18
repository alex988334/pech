<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
   /*     'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=admin_basemaster',
            'username' => 'admin_gradinas',
            'password' => 'AlbatroS160',
            'charset' => 'utf8',
        ],
  //      */
        
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=admin_basemaster1', //admin_basemaster1',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
         //          */
        
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
          //  'cache' => 'cache',                       //  опасная вещь, долго не мог переназначить роль пользователю
                                                        //  записывала роль в кэш сервера, вследствии чего не мог долго разобраться в правах доступа
        ],
        'session' => [
            'class' => 'yii\web\DbSession',
            'db' => 'db',
            'sessionTable' => 'session',
            'timeout' => 600
        ]
        // */
    ], 
    'language' => 'ru-RU',
];


