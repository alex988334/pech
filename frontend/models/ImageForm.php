<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\Session;

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
                'maxWidth' => 200, 'minHeight' => 50, 'maxHeight' => 200,],
        ];
    }   
    
    public function saveFile()
    {
        if ($this->validate()) {
            $name = date('YmdHis') . '.' .  $this->image_file->extension;
            $this->image_file->saveAs('uploads/image/' . $name);
            return $name;
        } else {
            return null;
        }
    }
    
}