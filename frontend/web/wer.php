<?php

header("Content-Type: text/html; charset=utf-8");

$mass = [];
$mass[] = 'login';
$mass[] = 'logim';
$mass[] = 'login';
$mass[] = 'loginm';
$mass[] = 'Login';
$mass[] = ' login';


/** ВЫВОД 
 *  строки можно сравнивать через ==
 *  && всегда отрабатывает второй операнд
 *  || отрабатывает только первый операнд если он равет TRUE
 */

echo '<textarea rows="15" cols="100">';
print_r($mass);
echo '</textarea>';
if ($mass[0] == $mass[1]) echo '<br>равны0-1';
else echo '<br>не равны0-1';
if (($mass[0] == $mass[2]) || foo()) echo '<br> равны0-2';
else    echo '<br> не равны0-2';
if ($mass[0]== $mass[3])    echo '<br> равны0-3';
else    echo '<br>не равны0-3';
if ($mass[0] == $mass[4])    echo '<br> равны 0-4';
else    echo '<br>не равны 0-4';
if ($mass[0] == $mass[5])    echo '<br> равны 0-5';
else    echo '<br>не равны 0-5';

function foo(){
    echo 'хрень';
    return false;
}

/*class Ar {	
public $model ;//= Array(Array('wer' => '1', 'qwe' => 2), Array('gre' => '232', 'mnb' => '324'));
public function ar(){
	$this->model = Array(Array('wer' => '1', 'qwe' => 2), Array('gre' => '232', 'mnb' => '324'));
	if (is_array($this->model[0])) {
	$a = 'model';
	echo '<br> это массив!';	
	echo $this->$a[0] ;//. '[0]';
}}}
$thi = new Ar();
//$thi->ar;
print_r($thi->model);
/*
if (isset($a) && $a === null) echo $a;
else echo '$a не объявлена';*/