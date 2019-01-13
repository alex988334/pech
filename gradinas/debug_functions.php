<?php

/**
 * @author Gradinas <gradinas.ru>
 */

/** 
 *  Функция для распечатки массива в читабельном виде 
 * 
 *  @param type $array - распечатываемый массив 
 *  
 *  @author Gradinas <gradinas.ru>
 */

function debugArray($array)
{
    echo '<div><pre>' . print_r($array, true) . '</pre></div>';
}
