<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\widgets;

use yii\base\Widget;

/**
 * Description of ShowFields
 *
 * @author Gradinas
 */
class ShowFields extends Widget
{
    public $fields;
    
    public function init() {
        parent::init();
        if ($this->fields === null) {
            throw new Exception("Список полей пуст");
        }
    }
    
    public function run() {
        parent::run();
        
        $options = '';
        
        foreach ($this->fields as $one) {
            
            $checked = ($one['visibility_field'] == 1) ? $checked = 'checked' : $checked = '';
            $options .= '<input type="checkbox" id="' . $one['id_table_field'] . '" style="margin-left: 15px;" '. $checked .'>'
                    . $one['alias'] .'</input>';
        }
        
        return '<div id="selectedFields">' . $options . '<button class="btn btn-success btn-sm" '
                . ' style="margin-left: 15px" id="save" onclick="saveSelectedFields()">Сохранить</button></div>';
    }
}
