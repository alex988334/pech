<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\Session;
use yii\helpers\FileHelper;
use common\models\FileManager;

/**
 * Login form
 */
class ImageForm extends Model
{
    public $image_file;
    public $id;
    
   /* public function attributeLabels() {
        return [
            'image_file' => 'Изображение',
        ];
    }*/
    
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['image_file'], 'required'],
            [['image_file'], 'image', 'extensions' => 'png, jpg', 'minWidth' => 50,
                'maxWidth' => 800, 'minHeight' => 50, 'maxHeight' => 800,],
        ];
    }   
    
    public function saveFile()
    {
        if ($this->validate()) {
            $name = date('YmdHis') . '.' .  $this->image_file->extension;
            $path = FileManager::FILES . '/'. FileManager::ADDRESS_ORDERS . '/';           
            if (!file_exists($path)) {
                FileHelper::createDirectory($path);
            }
            $this->image_file->saveAs($path . $name);
            return $name;
        } else {
            return null;
        }
    }
    
}