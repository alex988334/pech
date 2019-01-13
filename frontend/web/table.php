<?php


function debugArray($array)
{
    echo '<div><pre>' . print_r($array, true) . '</pre></div>';
}


$mysqli = new mysqli('localhost', 'root', '', 'admin_basemaster1');                 
$mysqli->set_charset("utf8");
if ($mysqli->connect_errno) { 
    echo '<br>Соединение с базой невозможно';
    exit();
} 
echo '<br>соединение бд установленно';
$mysqli->query('DELETE FROM `manager_table_grant` WHERE id > 0');
$mysqli->query('DELETE FROM `manager_table` WHERE id > 0');
$mysqli->query('ALTER TABLE `manager_table_grant` AUTO_INCREMENT=0');
$mysqli->query('ALTER TABLE `manager_table` AUTO_INCREMENT=0');


$res = $mysqli->query('SHOW TABLES FROM admin_basemaster1');

while ($row = mysqli_fetch_assoc($res)) {
    foreach ($row as $nameTable) {
        if ($nameTable != null) { $tables[] = $nameTable; }
    }
}

debugArray($tables);

$comments = $mysqli->query('SELECT column_name,column_comment FROM information_schema.columns' 
    . ' WHERE table_schema="admin_basemaster1" and table_name="' . '"');

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
            . ' WHERE table_schema="admin_basemaster1" and table_name="' . $name . '"';
    $res = $mysqli->query($query);
  //  echo $query;
    $comments = [];
//    debugArray($res);
    while ($row = mysqli_fetch_assoc($res)) $comments[$row['column_name']] = $row;
 //   debugArray($comments);
    
    $idPar = $id;
    $id++;
    $res = $mysqli->query('SHOW COLUMNS FROM ' . $name . ' FROM admin_basemaster1');
    
    while ($row = mysqli_fetch_assoc($res)) {
       // debugArray($row);
        if ($row['Field'] != null) {
            
            $query = 'INSERT INTO `manager_table`(`id`, `name`, `parent`, `alias`) VALUES ("'
            . $id . '", "'
            . $row['Field'] . '", "'
            . $idPar . '", "'
                    .  $comments[$row['Field']]['column_comment'] . '")';
    
            if (!$mysqli->query($query)) {        
                echo '<br>ОШИБКА вставки поля ' . $row['Field'] . ' таблицы <br>' . $name; 
                exit();
            }
            $fields[] = $row['Field']; 
            $id++;
        }
    }
    
    $massTables[$name] = $fields;
    
    $fields = null;
}

echo '<br><br>УСПЕХ заполнения manager_table<br>';

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
            if ($row['name'] == $one) $changeField = 0;
        }
        
        $query = 'INSERT INTO `manager_table_grant`(`id_manager`, `id_table_field`, `change_field`) VALUES ("'
            . $id . '", "'
            . $row['id'] . '", "'
            . $changeField . '")';
    
        if (!$mysqli->query($query)) {        
            echo '<br>ОШИБКА вставки поля ' . $row['Field'] . ' таблицы <br>' . $name, '<br>' . $mysqli->error; 
            exit();
        }
    }
}

echo '<br><br>УСПЕХ присвоения grants';
//debugArray($massTables);




