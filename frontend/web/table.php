<?php


function debugArray($array)
{
    echo '<div><pre>' . print_r($array, true) . '</pre></div>';
}
$base = 'admin_basemaster1'; //'18-02-2019';

$mysqli = new mysqli('localhost', 'root', '', $base); 
//$mysqli = new mysqli('localhost', 'admin_gradinas', 'AlbatroS160', 'admin_basemaster'); 
$mysqli->set_charset("utf8");
if ($mysqli->connect_errno) { 
    echo '<br>Соединение с базой невозможно';
    exit();
} 
echo '<br>соединение бд установленно';

function clearManagerTable(){
    global $mysqli;
    $mysqli->query('DELETE FROM `manager_table_grant` WHERE id > 0');
    $mysqli->query('DELETE FROM `manager_table` WHERE id > 0');
    $mysqli->query('ALTER TABLE `manager_table_grant` AUTO_INCREMENT=0');
    $mysqli->query('ALTER TABLE `manager_table` AUTO_INCREMENT=0'); 
}

function clearCOM(){
    global $mysqli;
    $mysqli->query('DELETE FROM `client_order_master` WHERE id > 0');
    $mysqli->query('ALTER TABLE `client_order_master` AUTO_INCREMENT=0');
}


function processingManagerTable(){
    global $mysqli, $base;
    $res = $mysqli->query('SHOW TABLES FROM `' . $base . '`');

    while ($row = mysqli_fetch_assoc($res)) {
        foreach ($row as $nameTable) {
            if ($nameTable != null) { $tables[] = $nameTable; }
        }
    }

    //debugArray($tables);

    $comments = $mysqli->query('SELECT column_name,column_comment FROM information_schema.columns' 
        . ' WHERE table_schema="'. $base .'" and table_name="' . '"');

    $massTables = [];
    $id = 1;
    foreach ($tables as $name) {

        $query = 'INSERT INTO `manager_table`(`id`, `name`, `parent`) VALUES ("'
                . $id . '", "'
                . $name . '", null)';

        if (!$mysqli->query($query)) {        
            echo '<br>ОШИБКА вставки названия таблицы <br>' . $name; 
            exit();
        }

        $query = 'SELECT column_name, column_comment FROM information_schema.columns' 
                . ' WHERE table_schema="'. $base .'" and table_name="' . $name . '"';
        $res = $mysqli->query($query);
      //  echo $query;
        $comments = [];
    //    debugArray($res);
        while ($row = mysqli_fetch_assoc($res)) $comments[$row['column_name']] = $row;
     //   debugArray($comments);

        $idPar = $id;
        $id++;
        $res = $mysqli->query('SHOW COLUMNS FROM `' . $name . '` FROM `' . $base . '`');

        while ($row = mysqli_fetch_assoc($res)) {
           // debugArray($row);
            
            if ($row['Field'] == 'old_id' || $row['Field'] == 'id_status_on_off') continue;
            
            if ($row['Field'] != null) {

                $query = 'INSERT INTO `manager_table`(`id`, `name`, `parent`, `alias`) VALUES ("'
                . $id . '", "'
                . $row['Field'] . '", "'
                . ($idPar ?? 'NULL') . '", "'
                        .  $comments[$row['Field']]['column_comment'] . '")';

                if (!$mysqli->query($query)) { 
                    echo '<br>query' . $query;
                    echo '<br>ОШИБКА вставки поля ' . $row['Field'] . ' таблицы ' . $name; 
                    exit();
                }
                $fields[] = $row['Field']; 
                $id++;
            }
        }

        $massTables[$name] = $fields;

        $fields = null;
    }

    echo '<br>start clone to manager_table...';
    
    $tables = [ 'client_order_master' => ["klient", "zakaz", "master"] ];
    $comments = [];
    foreach ($tables as $name => $clone) {

        $res = $mysqli->query('SELECT id FROM manager_table WHERE name="'. $name .'"');
        $parent_id = mysqli_fetch_assoc($res)['id'];   
        $comments[$name]['parent_id'] = $parent_id;    

        foreach ($clone as $one) {

            $res = $mysqli->query('SELECT id FROM manager_table WHERE name="'. $one .'"');
            $clone_by = mysqli_fetch_assoc($res)['id'];
            $comments[$name]['name'][$one]['clone_by'] = $clone_by;

            $query = 'SELECT column_name, column_comment FROM information_schema.columns '
                    . ' WHERE table_schema="'. $base .'" and table_name="'. $one .'"';        
            $res = $mysqli->query($query);       
            while ($row = mysqli_fetch_assoc($res)) 
                    $comments[$name]['name'][$one]['columns'][$row['column_name']] = $row;
        }

        //debugArray($comments);
        foreach ($comments as $key => $one) {
            foreach ($one['name'] as $ke => $table) {
                foreach ($table['columns'] as $k => $column) {
                    if ($column['column_name'] == 'id' || $column['column_name'] == 'id' 
                            || $column['column_name'] == 'id_master' 
                            || $column['column_name'] == 'id_klient' 
                            || $column['column_name'] == 'id_region' 
                            || $column['column_name'] == 'old_id' 
                            || $column['column_name'] == 'id_status_on_off') {
                                continue;
                            }
                   $query = 'INSERT INTO `manager_table`(`name`, `parent`, `alias`, `clone_by`) VALUES ("'
                        . $column['column_name'] . '", "'
                        . ($one['parent_id'] ?? 'NULL') . '", "'
                        .  $column['column_comment'] . '", "'
                        . $table['clone_by'] . '")';
                    if (!$mysqli->query($query)) {  
                        echo '<br>query clone =>' . $query;
                        echo '<br>ОШИБКА вставки поля ' . $row['Field'] . ' таблицы <br>' . $name; 
                        exit();
                    }
                    // */
                }
            }
        }
        echo '<br><br>УСПЕХ заполнения manager_table<br>';
    }
}


function processingManagerTableGrant(){
    global $mysqli;
    $res = $mysqli->query('SELECT id_manager FROM manager');

    while ($id = mysqli_fetch_assoc($res)['id_manager']) {

        $query = 'SELECT `item_name` FROM `auth_assignment` WHERE `user_id`=' . $id;

        $role = mysqli_fetch_assoc($mysqli->query($query))['item_name'];
     //   echo '<br> ID = '. $id, '<br> РОЛЬ = '. $role;
        $rule = [];
        if ($role == 'manager') {

            $rule = [
                    'cena', 'balans', 'balans_add', 
                    'balans_delete', 'start_balans', 'visibility_field', 'change_field',
                    'item_name', 'reyting', 'reyting_add', 'reyting_delete', 'start_reyting'
                ];
        }

     //   debugArray($rule);
        $fields = $mysqli->query('SELECT * FROM manager_table');
        while ($row = mysqli_fetch_assoc($fields)) {
         /*   echo '<br><br>ROW';
            debugArray($row);*/
          $changeField = 1;

            foreach ($rule as $one) {
                if ($row['name'] == $one) {
                    $changeField = 0;
                }
            }

            $query = 'INSERT INTO `manager_table_grant`(`id_manager`, `id_table_field`, `change_field`) VALUES ("'
                . $id . '", "'
                . $row['id'] . '", "'
                . $changeField . '")';

            if (!$mysqli->query($query)) {        
                echo '<br>ОШИБКА вставки поля ' . $row['name'] . ' таблицы <br>' . $mysqli->error; 
                exit();
            }
        }
    }

    echo '<br><br>УСПЕХ присвоения grants';
    //debugArray($massTables);
}


/* Миграция из klient_zakaz & master_zakaz в client_order_master */
function processingCOM()
{
    global $mysqli;
    echo '<br>processing client_order_master...';
    
    $res = $mysqli->query('SELECT * FROM klient_vs_zakaz');
    while ($row = mysqli_fetch_assoc($res)) {
        $massRows[] = $row;
    }
    
    if (!count($massRows)) {
        echo '<br>0 записей в klient_vs_zakaz';
        exit();
    }
    $date = date('U');
    $uspexKl = 0;
    $uspexMas = 0;
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
       /* if ($res['id_region'] != $res1['id_region']) {       
                echo '<br> Обнаружено нарушение в связке клиент-заявка по региону, id = ' . $one['id'] 
                        . ', id_klient = ' . $one['id_klient']
                        . ', id_zakaz = ' . $one['id_zakaz'];
                $i++;
    //        return;
                continue;
        }  // */      
        
        $query = 'INSERT INTO `client_order_master` (`id_client`, `id_order`, `created_at`, `id_region`) VALUES ('
                . $one['id_klient'] . ', '
                . $one['id_zakaz'] . ', '
                . $date . ', '
                . ($res['id_region'] ?? $res1['id_region']) . ')';
        if (!$mysqli->query($query)) {
            echo '<br> Ошибка вставки связки, $query = ' . $query;
            exit();
        } else {
            $uspexKl++;
        }        
    }
    echo '<br>total = ' . $i;
    echo '<br>успех миграции klient_zakaz -> client_order_master => ' . $uspexKl;
    
    $massRows1 = [];
    $res = $mysqli->query('SELECT * FROM master_vs_zakaz');
    while ($row = mysqli_fetch_assoc($res)) {
        $massRows1[] = $row;
    }
    
    if (!count($massRows1)) {
        echo '<br>0 записей в master_vs_zakaz';
        exit();
    }
    $i = 0;
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
                        . ', id_klient = ' . $one['id_master']
                        . ', id_zakaz = ' . $one['id_zakaz'];
                $i++;
    //        return;
            continue;
        }        
        
      
        $query = 'UPDATE `client_order_master` SET `id_master`='. $one['id_master'] .' WHERE `id_order`=' 
                . $one['id_zakaz'];
      //  echo '<br>' . $query;
        if (!$mysqli->query($query)) {
            echo '<br> Ошибка вставки связки, $query = ' . $query;
            exit();
        } else {
            $uspexMas++;  
        }
    }

      echo '<br>total = ' . $i;
    echo '<br>успех миграции master_zakaz -> client_order_master => ' . $uspexMas;
    
}


clearCOM();
clearManagerTable();
processingManagerTable();
processingManagerTableGrant();


processingCOM();
//securityCOM();
