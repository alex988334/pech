<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'enableCsrfValidation' => true,
            'enableCsrfCookie' => true,
            'enableCookieValidation' => true,
            
    //        'csrfParam' => '_csrf-frontend',
            
        ],
        'user' => [            
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => false,
            
            'authTimeout' => 500,
     //       'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            'class' => 'yii\web\DbSession',
            'db' => 'db',
            'sessionTable' => 'session',
            'timeout' => 600,
            'writeCallback' => function ($session) {
                return [
                    'user_id' => Yii::$app->user->id, 
                    'last_time' => time()
                ];
            }
        ], // */
     /* было по умолчанию  */
  /*      'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
        ],//  */
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'site/signup' => '/site/signup'
            ],
        ],
        
      /*  'assetManager' => [
            'bundles' => [
                'dosamigos\google\maps\MapAsset' => [
                    'options' => [
                        'key' => 'AIzaSyATSLFGTMVV62tf0BwTUO4waBqjN0nQzH0',
                        'language' => 'ru',
                        'libraries' => 'places',
                    //    'v' => '3.exp',
                    //    'sensor'=> 'false',
                        'version' => '3.1.18'
                    ]
                ]
            ]
        ],*/
               
       /* 'yandexMapsApi' => [
            'class' => 'mirocow\yandexmaps\Api',
            
        ]
        */
    ],
    'params' => $params,
];
