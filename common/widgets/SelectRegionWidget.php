<?php

namespace common\widgets;

use yii\base\Widget;


class SelectRegionWidget extends Widget 
{
    public $listRegion;
    public $selectedRegion;
    
    public function init() {
        parent::init();
        
        if ($this->listRegion === null) { $this->listRegion = []; }
        if ($this->selectedRegion === null) { $this->selectedRegion = 1; }
    }
    
    public function run() {
      // parent::run();
        
        $options = '';
        
        foreach ($this->listRegion as $one) {
            
            $select = ($one['id'] == $this->selectedRegion) ? 'selected' : '';
            
            $options .= '<option value="' . $one['id'] . '"' 
                    . ' label="' . $one['name'] . '" ' . $select . '>'
                    . '</option>';
        }
     //   Yii::debug($options);
        return  '<font size="4" color="white">Выберите регион </font><select id="vid_region" style="font-size : 11pt; padding : 3px; border-radius : 5px; background: transparent;
            background-color: white; ">' . $options . '</select></div>'; 
    }
    
}