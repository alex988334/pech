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
    /**
     * Sample: [ 
     *      0 => [
     *          'name' => '...', 
     *          'alias' => '...',
     *          'id_table_field' => '...',
     *          'visibility_field' => '...',
     *      ], 
     *      ...,    
     * ]
     *      
     * @var array $fields
     */
    public $fields;    
    /**
     * Sample: ['name_field_1' => 'name_field_1', 
     *          'name_field_2' => 'name_field_2', ...]
     * @var array $blackFields
     */
    public $blackFields = [];    
    /**
     *
     * @var int $colums
     */
    public $colums = 4;
    
    public function init() {
        parent::init();
        if ($this->fields === null || count($this->fields) == 0) {
            throw new Exception("Список полей пуст");
        }
    }
    
    public function run() {
        parent::run(); 
        
        if (count($this->blackFields)) {
            foreach ($this->fields as $one) {
                if (!key_exists($one['name'], $this->blackFields)) {
                    $mass[] = $one;
                }
            }
            if (count($mass)) $this->fields = $mass;
        }
        
        $total = count($this->fields);        
        while (($total % $this->colums) != 0) {
            $total++;
        }
        
        $totalRows = (int)($total / $this->colums);
        if ($this->colums <= 0) {
            $this->colums = 4;
        } elseif ($this->colums <= 4) {
            $bootstarap = (int)(12 / $this->colums);
        } elseif ($this->colums <= 6) {            
            $bootstarap = (int)(12 / $this->colums);
        } else {
            $this->colums = 6;
            $bootstarap = (int)(12 / $this->colums);
        }
        
        reset($this->fields);
        $flag = TRUE;
        $str = '<div class="row"><div class="col" style="text-shadow: 2px 2px 2px grey; text-align: center;'
                . 'color: whitesmoke;border-radius: 5px;"><h4>Списки отображаемых полей</h4></div></div>'
                . '<div class="row">';
        for ($i = 1; $i <= $this->colums; $i++) {
            $str .= '<div class="col-sm-'. $bootstarap .' padd-sm"><div class="colum">';
            for ($k = 1; ($k <= $totalRows & $flag); $k++) {
                $ind = (int) key($this->fields);             
                $checked = ($this->fields[$ind]['visibility_field'] == 1) ?  $checked = 'checked' : $checked = '';
                $str .= '<input type="checkbox" id="' . $this->fields[$ind]['id_table_field'] 
                        . '" style="margin-left: 15px;" '. $checked .'><span style="padding-left: 10px;">'
                        . $this->fields[$ind]['alias'] .'</span></input><br>';   
                $flag = next($this->fields);
            }
            $str .= '</div></div>';
        }
        $str .= '</div><div class="row"><div class="col"><button class="btn btn-warning btn-block"'
                . ' id="save" onclick="saveSelectedFields()">Сохранить</button></div></div>';
        
        
        
     /*   foreach ($this->fields as $one) {          
            $checked = ($one['visibility_field'] == 1) ? $checked = 'checked' : $checked = '';
            $options .= '<input type="checkbox" id="' . $one['id_table_field'] . '" style="margin-left: 15px;" '. $checked .'>'
                    . $one['alias'] .'</input>';
        }
        
        return '<div id="selectedFields" class="show-table-fields-widjet">' . $options . '<button class="btn btn-success btn-sm" '
                . ' style="margin-left: 15px" id="save" onclick="saveSelectedFields()">Сохранить</button></div>';
      */
      /*echo $this->fields[0]['visibility_field'] ;  */
        return '<div id="selectedFields" class="container">' . $str 
           //     . '<button class="btn btn-success btn-sm" '
          //      . ' style="margin-left: 15px" id="save" onclick="saveSelectedFields()">Сохранить</button>'
                . '</div>';
    
    //    */
    }
}
