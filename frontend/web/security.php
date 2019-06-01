<?php

function debugArray($array)
{
    echo '<div><pre>' . print_r($array, true) . '</pre></div>';
}

 
$mysqli = new mysqli('localhost', 'admin_gradinas', 'AlbatroS160', 'admin_basemaster'); 
$mysqli->set_charset("utf8");
if ($mysqli->connect_errno) { 
    echo '<br>Соединение с базой невозможно';
    exit();
} 
echo '<br>соединение бд установленно';

function securityCOM()
{
    global $mysqli;
    echo '<br>processing client_order_master...';
    $res = $mysqli->query('SELECT * FROM klient_vs_zakaz');
    while ($row = mysqli_fetch_assoc($res)) $massRows[] = $row;
    
    if (!count($massRows)) {
        echo '<br>0 записей в klient_vs_zakaz';
        return;
    }
    $i = 0;
    foreach ($massRows as $one) {
        $res = $mysqli->query('SELECT id_region FROM klient WHERE id_klient=' . $one['id_klient'] . ' LIMIT 1');
        $res = mysqli_fetch_assoc($res);      
     
        $res1 = $mysqli->query('SELECT id_region FROM zakaz WHERE id=' . $one['id_zakaz'] . ' LIMIT 1');
        $res1 = mysqli_fetch_assoc($res1);
       
        
        if (empty($res)) {      
                echo '<br> Обнаружено нарушение NULL, id_klient = ' . $one['id_klient'];
                $i++;
                continue;
    //        return;   
        }
        if (empty($res1)) {      
                echo '<br> Обнаружено нарушение NULL, id_zakaz = ' . $one['id_zakaz'];
                $i++;
    //        return;
                continue;
        }
        if ($res['id_region'] != $res1['id_region']) {       
                echo '<br> Обнаружено нарушение в связке клиент-заявка по региону, id = ' . $one['id'] 
                        . ', id_klient = ' . $one['id_klient']
                        . ', id_zakaz = ' . $one['id_zakaz'];
                $i++;
    //        return;
                continue;
        }        
    }
    echo '<br>total = ' . $i;
    $res = $mysqli->query('SELECT * FROM master_vs_zakaz');
    while ($row = mysqli_fetch_assoc($res)) $massRows1[] = $row;
    
    if (!count($massRows1)) {
        echo '<br>0 записей в master_vs_zakaz';
        return;
    }
    foreach ($massRows1 as $one) { 
        $res = $mysqli->query('SELECT id_region FROM master WHERE id_master=' . $one['id_master'] . ' LIMIT 1');
        $res = mysqli_fetch_assoc($res);  
     
        $res1 = $mysqli->query('SELECT id_region FROM zakaz WHERE id=' . $one['id_zakaz'] . ' LIMIT 1');
        $res1 = mysqli_fetch_assoc($res1);
       
        
        if (empty($res)) {      
                echo '<br> Обнаружено нарушение NULL, id_master = ' . $one['id_master'];
                $i++;
                continue;
    //        return;   
        }
        if (empty($res1)) {      
                echo '<br> Обнаружено нарушение NULL, id_zakaz = ' . $one['id_zakaz'];
                $i++;
    //        return;
                continue;
        }
        if ($res['id_region'] != $res1['id_region']) {       
                echo '<br> Обнаружено нарушение в связке мастер-заявка по региону, id = ' . $one['id'] 
                        . ', id_master = ' . $one['id_master']
                        . ', id_zakaz = ' . $one['id'];
                $i++;
    //        return;
                continue;
        }           
    }
    echo '<br>total = ' . $i;
}

securityCOM();