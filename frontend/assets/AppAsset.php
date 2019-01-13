<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
     //   '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css',
    ];
    public $js = [
        
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
     //   '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css',
    //    '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js',
        
    ]; 
    
}
