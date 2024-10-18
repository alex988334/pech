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
class ChatImageForm extends Model
{
    /**
     *
     * @var file $file
     */
    public $file;
    /**
     *
     * @var int $id_chat 
     */
    public $id_chat;
    
   /* public function attributeLabels() {
        return [
            'image_file' => 'Изображение',
        ];
    }*/
    
    public $error;
    
    public function rules()
    {
        return [
            [['id_chat'], 'integer'],
            [['file', 'id_chat'], 'required'],
            [['file'], 'file', 'maxSize' => 30000000],
          /*  [['file'], 'image', 'extensions' => 'png, jpg', 'minWidth' => 50,
                'maxWidth' => 200, 'minHeight' => 50, 'maxHeight' => 200,],*/
        ];
    }   
    
    public function saveFile()
    {
        if ($this->validate()) {
            $name = date('YmdHis') . '.' .  $this->file->extension;            
            $path = Yii::getAlias('@web') . FileManager::FILES . '/'. FileManager::ADDRESS_CHATS . '/' . $this->id_chat;
            
            if (!is_dir($path)) {
                try {
                    if (!FileHelper::createDirectory($path)){
                        $this->error = 'ошибка при создании папки';
                        return null;                        
                    } 
                } catch (\yii\base\Exception $ex) { 
                    $this->error = 'EXCEPTION - ошибка при создании папки; $path => ' .
                            $path . ' $ex=' . $ex;
                    return null;                     
                }
            }           
            
            if ($this->file->saveAs($path . '/' . $name)) return $name;
        } else {
            $this->error = $this->errors;
        }
        
        return null;
    }
    
}