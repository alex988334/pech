<?php
set_time_limit(500);
head();

function head()
{
    clearBD();    
    masterToMaster(); 
    klientToKlient();
    regionToManager();
    zakazAktivToZakaz();
    zakazGotovToZakaz();
//   */
}


function regionToManager()
{
    $mysqli_old = createBD('old');
    $mysqli = createBD('ewq'); 
    $query = 'SELECT `ID_REGION`, `NAME_REGION`, `DOLGOTA`, `SHIROTA`, `PHONE1`, `PHONE2`, `PHONE3` FROM `vid_region`'; 
    $result = $mysqli_old->query($query);
    $total = mysqli_num_rows($result);
    echo '<br>manager count = ', $total;
   // debugArray($result);
    $mysqli_old->close();
    
    $id = mysqli_fetch_assoc($mysqli->query('SELECT MAX(id) AS id FROM `user`'))['id'] + 1;
    
    $login = [ 'irkutsk', 'krasnoyarsk', 'ulan-ude', 'head_irkutsk', 'head_krasnoyarsk', 'head_ulan-ude'];
    $role = ['manager', 'manager', 'manager', 'head_manager', 'head_manager', 'head_manager'];
    $pass = ['pHLd3rf5hdu3Lr', 'epLj43MvhvidEF', 'Tpv6dNvpJxL9j1', 'glava', 'glava', 'glava'];
    $idRegion = [1, 2 , 3, 1, 2, 3];
    for ($k = 0; $k < 6; $k++) {
        $auth_key[] = substr(password_hash($login[$k], PASSWORD_DEFAULT), 0, 32);
        $password[] = password_hash($pass[$k], PASSWORD_DEFAULT);
    }
    $k = 0;
    $time = time();
    while ($row = mysqli_fetch_assoc($result)) { 
        
        $query = 'INSERT INTO `user`(`id`, `username`, `auth_key`, `password_hash`,'
                . ' `created_at`, `updated_at`) VALUES ("'
                . $id . '", "' . $login[$k] . '", "' . $auth_key[$k] . '", "'
                . $password[$k] . '", "' . $time . '", "' . $time . '")';
        
        if ($mysqli->query($query)) { /*echo '<br>вставка была';*/ } 
        else { echo '<br>', $query, '<br> - user_manager вставка не была ID = ' 
                . $id, '<br>', $mysqli->error; 
            exit();
        }
        
        $query = 'INSERT INTO `auth_assignment`(`item_name`, `user_id`) VALUES ("'. $role[$k] .'", "'. $id . '")';
        if ($mysqli->query($query)) { /*echo '<br>вставка была';*/ } 
        else { echo '<br>', $query, '<br> - auth_assignment manager вставка не была ID = ' 
                . $id, '<br>', $mysqli->error; 
            exit();
        }
        
        $query = 'INSERT INTO `manager`(`id_manager`, `familiya`, `imya`, `otchestvo`, `id_region`, `phone1`) VALUES ("'
                . $id . '", "", "", "", "'
                . $idRegion[$k] . '", "'
                . $row['PHONE1'] . '")';
        
        if ($mysqli->query($query)) { /*echo '<br>вставка была';*/ } 
        else { echo '<br>', $query, '<br> - manager вставка не была ID = ' 
                . $id, '<br>', $mysqli->error; 
            exit();
        }
        $id++;
        $k++;
    }
    $phone = [0, 0, 0, 1, 2, 3];
    for ($i=3; $i<6; $i++) {
        $query = 'INSERT INTO `user`(`id`, `username`, `auth_key`, `password_hash`,'
                    . ' `created_at`, `updated_at`) VALUES ("'
                    . $id . '", "' . $login[$i] . '", "' . $auth_key[$i] . '", "'
                    . $password[$i] . '", "' . $time . '", "' . $time . '")';

        if ($mysqli->query($query)) { /*echo '<br>вставка была';*/ } 
        else { echo '<br>', $query, '<br> - user_manager вставка не была ID = ' 
                . $id, '<br>', $mysqli->error; 
            exit();
        }

        $query = 'INSERT INTO `auth_assignment`(`item_name`, `user_id`) VALUES ("'. $role[$i] .'", "'. $id . '")';
        if ($mysqli->query($query)) { /*echo '<br>вставка была';*/ } 
        else { echo '<br>', $query, '<br> - auth_assignment manager вставка не была ID = ' 
                . $id, '<br>', $mysqli->error; 
            exit();
        }

        $query = 'INSERT INTO `manager`(`id_manager`, `familiya`, `imya`, `otchestvo`, `id_region`, `phone1`) VALUES ("'
                . $id . '", "", "", "", "'
                . $idRegion[$i] . '", "' . $phone[$i] . '")';

        if ($mysqli->query($query)) { /*echo '<br>вставка была';*/ } 
        else { echo '<br>', $query, '<br> - manager вставка не была ID = ' 
                . $id, '<br>', $mysqli->error; 
            exit();
        }
        $id++;
    }
    echo '<br>УСПЕХ manager USER';
    echo '<br>УСПЕХ manager ROLE';
    echo '<br>УСПЕХ менеджеров<br>';
    $mysqli->close();    
    $result->free();
    
}

function klientToKlient()
{
 //   INSERT INTO `klient`(`id`, `id_klient`, `imya`, `familiya`, `otchestvo`, `vozrast`, `phone`, `reyting`, `balans`, `id_region`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5],[value-6],[value-7],[value-8],[value-9],[value-10])
    
    $mysqli_old = createBD('old');
    $mysqli = createBD('ewq'); 
    $query = 'SELECT * FROM `klient`'/* WHERE `TELEFON` IS NOT NULL'*/; // GROUP BY `TELEFON`';
    $result = $mysqli_old->query($query);
    $total = mysqli_num_rows($result);
    echo '<br>klient count = ', $total;
   // debugArray($result);
    $mysqli_old->close();
    
    $id = mysqli_fetch_assoc($mysqli->query('SELECT MAX(id) AS id FROM `user`'))['id'] + 1;
    
    while ($row = mysqli_fetch_assoc($result)) { 
        
        $login = $row['LOGIN'] ?? $row['TELEFON'];
        if ($login == null) { 
            echo 'ОШИБКА, у клиента логин и телефон неопределены, ID', $row['ID_KLIENTA'];
            exit();
        } 
        
        $auth_key = substr(password_hash($login, PASSWORD_DEFAULT), 0, 32);
        $password = password_hash($row['PAROL'], PASSWORD_DEFAULT);
        $time = time();
        
        $query = 'INSERT INTO `user`(`id`, `username`, `auth_key`, `password_hash`,'
                . ' `created_at`, `updated_at`) VALUES ("'
                . $id . '", "' . $login . '", "' . $auth_key . '", "'
                . $password . '", "' . $time . '", "' . $time . '")';
        
        if ($mysqli->query($query)) { /*echo '<br>вставка была';*/ } 
        else { echo '<br>', $query, '<br> - user вставка не была oldID = ' 
                . $row['ID_KLIENTA'], ' newID = ', $id, '<br>', $mysqli->error; 
            exit();
        }
        
        $query = 'INSERT INTO `auth_assignment`(`item_name`, `user_id`) VALUES ("klient", "'. $id . '")';
        if ($mysqli->query($query)) { /*echo '<br>вставка была';*/ } 
        else { echo '<br>', $query, '<br> - auth_assignment вставка не была oldID = ' 
                . $row['ID_KLIENTA'], ' newID = ', $id, '<br>', $mysqli->error; 
            exit();
        }
        
        $query = 'INSERT INTO `klient`(`id_klient`, `imya`, `familiya`, '
                . '`otchestvo`, `vozrast`, `phone`, `reyting`, `balans`, '
                . '`id_region`, `old_id`) VALUES ("'
                . $id . '", "' . $row['IMYA'] . '", "' . $row['FAMILIYA'] . '", "'
                . $row['OTCHESTVO'] . '", "' . $row['VOZRAST'] . '", "'
                . $row['TELEFON'] . '", "' . $row['REITING'] . '", "'
                . $row['BALANS'] . '", "' . $row['REGION'] . '", "' . $row['ID_KLIENTA'] .'")';
        
        if ($mysqli->query($query)) { /*echo '<br>вставка была';*/ } 
        else { echo '<br>', $query, '<br> - head вставка не была ID = ' 
                . $row['ID_KLIENTA'], '<br>', $mysqli->error; 
            exit();
        }
        $id++;
    }
    echo '<br>УСПЕХ klient USER';
    echo '<br>УСПЕХ klient ROLE';
    echo '<br>УСПЕХ клиентов<br>';
    $mysqli->close();    
    $result->free();
}


function masterToMaster()
{
    $mysqli_old = createBD('old');
    $mysqli = createBD('ewq'); 
    $query = 'SELECT * FROM `master`'/* WHERE `ID_MASTERA` NOT IN (4, 5, 7, 8, 9, 11, '
            . '12, 41, 57, 84, 86, 89, 90, 91, 92, 94, 95, 99, 100, 101, 102, 103, '
            . '105, 130, 133) AND TELEFON IS NOT NULL'; // GROUP BY `TELEFON`'*/;
    $result = $mysqli_old->query($query);
    $total = mysqli_num_rows($result);
    echo '<br> master count = ', $total;
   // debugArray($result);
    $mysqli_old->close();
    
    $id = 1;
    while ($row = mysqli_fetch_assoc($result)) { 
        $login = $row['LOGIN'] ?? $row['TELEFON'];
        if ($login == null) { 
            echo 'ОШИБКА, у мастера логин и телефон неопределены, ID', $row['ID_MASTERA'];
            exit();
        } 
        
        $auth_key = substr(password_hash($login, PASSWORD_DEFAULT), 0, 32);
    //    password_verify($model->password, $user->password_hash)
        $password = password_hash($row['PAROL'], PASSWORD_DEFAULT);
        $time = time();
        
        $query = 'INSERT INTO `user`(`id`, `username`, `auth_key`, `password_hash`,'
                . ' `created_at`, `updated_at`) VALUES ("'
                . $id . '", "' . $login . '", "' . $auth_key . '", "'
                . $password . '", "' . $time . '", "' . $time . '")';
        
        if ($mysqli->query($query)) { /*echo '<br>вставка была';*/ } 
        else { echo '<br>', $query, '<br> - user вставка не была oldID = ' 
                . $row['ID_MASTERA'], ' newID = ', $id, '<br>', $mysqli->error; 
            exit();
        }
        
        $query = 'INSERT INTO `auth_assignment`(`item_name`, `user_id`) VALUES ("master", "'. $id . '")';
        if ($mysqli->query($query)) { /*echo '<br>вставка была';*/ } 
        else { echo '<br>', $query, '<br> - auth_assignment вставка не была oldID = ' 
                . $row['ID_MASTERA'], ' newID = ', $id, '<br>', $mysqli->error; 
            exit();
        }
        
        $massNavik = json_decode($row['NAVIKI'], TRUE);
        if ($massNavik == NULL) { echo '<br> ОШИБКА при декодированиие навыков мастера ID = ', $row['ID_MASTERA']; exit();}
        foreach ($massNavik as $work => $navik) {
            $quer = 'INSERT INTO `master_work_navik`(`id_master`, `id_vid_work`, `id_vid_navik`) VALUES ("'
                    . $id /*$row['ID_MASTERA'] */ . '", "'
                    . $work . '", "'
                    . $navik . '")';
            if ($mysqli->query($quer)) { /*echo '<br>вставка была';*/ } 
            else { echo '<br>', $quer, '<br> - navik вставка не была ID = ' 
                    . $row['ID_MASTERA'], '<br>', $mysqli->error; 
                exit();
            }
        }
        
        $query = 'INSERT INTO `master`(`id_master`, `familiya`, `imya`, `otchestvo`, '
                . '`id_status_on_off`, `vozrast`, `staj`, `reyting`, `id_status_work`, '
                . '`data_registry`, `data_unregistry`, `phone`, `mesto_jitelstva`, '
                . '`mesto_raboti`, `balans`, `id_region`, `old_id`) VALUES ("'
                . $id /*$row['ID_MASTERA']*/ . '", "' . $row['FAMILIYA'] . '", "'
                . $row['IMYA'] . '", "' . $row['OTCHESTVO'] . '", "'
                . $row['PODKLYUCHENIE'] . '", "' . $row['VOZRAST'] . '", "'
                . $row['STAJ'] . '", "' . $row['REYTING'] . '", "'
                . $row['STATUS_WORK'] . '", "' . $row['DATA_REGISTR'] . '", "'
                . $row['DATA_UVOLNENIYA'] . '", "' . $row['TELEFON'] . '", "'
                . $row['MESTO_JITELSTVA'] . '", "' . $row['MESTO_RABOTI'] . '", "'
                . $row['BALANS'] . '", "' . $row['REGION'] . '", "' . $row['ID_MASTERA'] . '")';
        
        /* stat_rabot 0 - партнер, 1 - работник новое 9 - партнер, 10 - работник */
        /* podklyuch 0 - нет, 1- да  в нов 7 - да, 8 - нет */
        if ($mysqli->query($query)) { /*echo '<br>вставка была';*/ } 
        else { echo '<br>', $query, '<br> - head вставка не была ID = ' 
                . $row['ID_MASTERA'], '<br>', $mysqli->error; 
            exit();
        }
        $id++;
    }
    echo '<br>УСПЕХ master USER';
    echo '<br>УСПЕХ master ROLE';
    echo '<br>УСПЕХ навыков';
    echo '<br>УСПЕХ мастера<br>';
    $mysqli->close();    
    $result->free();
}


function zakazGotovToZakaz()
{
    $mysqli_old = createBD('old');
    $mysqli = createBD('ewq'); 
    $query = 'SELECT * FROM `zakaz_gotov`'/* WHERE `ID_KLIENTA` IS NOT NULL AND `ID_MASTERA` IS NOT NULL ORDER BY `ID`'*/;
    $result = $mysqli_old->query($query);
    echo '<br>zakaz gotov = ', mysqli_num_rows($result);
 //   debugArray($result);
    $mysqli_old->close();
    
    $id = mysqli_fetch_assoc($mysqli->query('SELECT MAX(id) AS id FROM `zakaz`'))['id'] + 1;
    
    while ($row = mysqli_fetch_assoc($result)) { 
    //    debugArray($row);
        
      //  $erVal = [ 4, 5, 7, 8, 9, 11, 12, 41, 57, 84, 86, 89, 90, 91, 92, 94, 95, 99, 100, 101, 102, 103, 105, 130, 133 ];
     //   if (!array_search($row['ID_MASTERA'], $erVal)) {
            $query = 'SELECT id_master AS id FROM master WHERE old_id=' . $row['ID_MASTERA'];
            $idMaster = mysqli_fetch_assoc($mysqli->query($query))['id'];
            
            $query = 'INSERT INTO `history_zakaz`(`id_zakaz`, `id_master`, '
                    . '`id_status`, `time`, `date`, `izmeneniya`) VALUES ("'
                    . $id . '", "' . $idMaster . '", "5", "' . time() . '", "'
                    . date('Y-m-d') . '", "")';
            if ($mysqli->query($query)) { echo '<br>вставка была id_old=' 
                    . $row['ID'], '<br>id_new=' . $id; } 
            else { echo '<br>', $query, '<br> - history вставка не была id_old=' 
                    . $row['ID'], '<br>id_new=' . $id . '<br>',  $mysqli->error; 
                exit();
            }
   //     }
        
        $query = 'INSERT INTO `zakaz`(`id`, `id_vid_work`, `id_navik`, `name`, '
                . '`cena`, `opisanie`, `reyting_start`, `gorod`, `poselok`, '
                . '`ulica`, `dom`, `kvartira`, `id_status_zakaz`, `id_shag`, '
                . '`data_registry`, `data_start`, `data_end`, `dolgota`, '
                . '`shirota`, `dolgota_change`, `shirota_change`, '
                . '`id_region`) VALUES ("'
                . $id . '", "' . $row['ID_VID'] . '", "1", "'
                . $row['NAZVANIE'] . '", "' . $row['CENA'] . '", "'
                . $row['OPISANIE'] . '", "' . $row['REYTING_START'] . '", "'
                . $row['GOROD'] . '", "' . $row['POSELOK'] . '", "'
                . $row['ULICA'] . '", "' . $row['DOM'] . '", "'
                . $row['KVARTIRA'] . '", "5", "6", "'
                . $row['DATA_PODACHI'] . '", "' . $row['DATA_START'] . '", "'
                . $row['DATA_END'] . '", "0", "0", "0", "0", "' . $row['REGION'] . '")';
        if ($mysqli->query($query)) { /*echo '<br>вставка была';*/ } 
        else { echo '<br>', $query, '<br> - head вставка не была ID = ' 
                . $row['ID'], '<br>', $mysqli->error; 
            exit();
        }
        $id++;
    }
    echo '<br>УСПЕХ истории';
    echo '<br>УСПЕХ заявки выполненные<br>';
    $mysqli->close();    
    $result->free();
}


/*  migration zakaz_aktiv into zakaz, klient_vs_zakaz, master_vs_zakaz  */
function zakazAktivToZakaz()
{            
    $mysqli_old = createBD('old');
    $mysqli = createBD('ewq'); 
    $query = 'SELECT a.`ID`, a.`ID_KLIENTA`, a.`ID_MASTERA`, a.`ID_VID`,
        a.`OCENKA`, a.`NAZVANIE`, a.`CENA`, a.`OPISANIE`, a.`REYTING_START`,
        a.`ZAMETKA`, a.`GOROD`, a.`POSELOK`, a.`ULICA`, a.`DOM`, a.`KVARTIRA`,
        a.`STATUS`, a.`SHAG`, a.`DATA_PODACHI`, a.`DATA_START`, a.`DATA_END`,
        a.`AUKCION`, a.`DOLGOTA`, a.`SHIROTA`, a.`DOLGOTA_ISK`, a.`SHIROTA_ISK`,
        a.`IMAGE`, a.`REGION` FROM `zakaz_aktiv` a 
        INNER JOIN klient k ON k.id_klienta=a.`ID_KLIENTA`';
    
    $res = $mysqli_old->query($query);
    $total = mysqli_num_rows($res);
    echo '<br>zakaz aktiv = ', $total;
  //  debugArray($res);  
    while ($row = mysqli_fetch_assoc($res)) {    
        
      //  if ($row['ID_KLIENTA'] != null && $row['ID_KLIENTA'] != '0') {
            $query = 'SELECT id_klient AS id FROM klient WHERE old_id=' . $row['ID_KLIENTA'];
            $idKlient = mysqli_fetch_assoc($mysqli->query($query))['id'];
        
        //    exit();
            $quer = 'INSERT INTO `klient_vs_zakaz`(`id_klient`, `id_zakaz`) VALUES ('
                   . $idKlient .', ' . $row['ID'] . ')';
            
            if ($mysqli->query($quer)) { /*echo '<br> + klient';*/ }
            else  {echo '<br>', $quer, '<br> - klient', $mysqli->error; 
                exit();
            }
       // }
        
     //   $erVal = [ 4, 5, 7, 8, 9, 11, 12, 41, 57, 84, 86, 89, 90, 91, 92, 94, 95, 99, 100, 101, 102, 103, 105, 130, 133 ];
        if ($row['ID_MASTERA'] != null /*&& !array_search($row['ID_MASTERA'], $erVal)*/) {
            
            $query = 'SELECT id_master AS id FROM master WHERE old_id=' . $row['ID_MASTERA'];
            $idMaster = mysqli_fetch_assoc($mysqli->query($query))['id'];
            
            $quer = 'INSERT INTO `master_vs_zakaz`(`id_master`, `id_zakaz`) VALUES ('
                   . $idMaster .', ' . $row['ID'] . ')';
            
            if ($mysqli->query($quer)) { /* echo '<br> + master';*/} 
            else { echo '<br>', $quer, '<br> - master<br>$idMaster=', $idMaster,
                    '<br>$row["ID_MASTERA"]=' . $row['ID_MASTERA'], '<br>error=> ' , $mysqli->error; 
                exit();
            }
        }

        if ($row['SHAG'] == null) {
            $row['SHAG'] = '1';
        } 
        $query1 = 'INSERT INTO `zakaz`(`id`, `id_vid_work`, `id_navik`, `name`, '
                . '`cena`, `opisanie`, `reyting_start`, `zametka`, `gorod`, '
                . '`poselok`, `ulica`, `dom`, `kvartira`, `id_status_zakaz`, '
                . '`id_shag`, `data_registry`, `data_start`, `data_end`, '
                . '`dolgota`, `shirota`, `dolgota_change`, `shirota_change`, '
                . '`image`, `id_region`) '
                . 'VALUES ("'
                . $row['ID'] . '", "' . $row['ID_VID'] . '", "' . $row['OCENKA'] . '", "'  
                . $row['NAZVANIE'] . '", "' . $row['CENA'] . '", "' . $row['OPISANIE'] . '", "'
                . $row['REYTING_START'] . '", "' . $row['ZAMETKA'] . '", "'
                . $row['GOROD'] . '", "' . $row['POSELOK'] . '", "' . $row['ULICA'] . '", "'
                . $row['DOM'] . '", "' . $row['KVARTIRA'] . '", "' . $row['STATUS'] . '", "'
                . $row['SHAG'] . '", "' . $row['DATA_PODACHI'] . '", "'
                . $row['DATA_START'] . '", "' . $row['DATA_END'] . '", "'
                . $row['DOLGOTA'] . '", "' . $row['SHIROTA'] . '", "'
                . $row['DOLGOTA_ISK'] . '", "' . $row['SHIROTA_ISK'] . '", "'
                . $row['IMAGE'] . '", "' . $row['REGION'] . '")';

        if ($mysqli->query($query1)) { /*echo '<br>вставка была';*/ } 
        else { echo '<br>', $query1, '<br> - head вставка не была ID = ' 
                . $row['ID'], '<br>', $mysqli->error; 
            exit();
        }
    }  
    $res->free();
    $mysqli_old->close();
    $mysqli->close();  
    echo '<br>УСПЕХ клиенты и заявки';
    echo '<br>УСПЕХ мастера и заявки';
    echo '<br>УСПЕХ активные заявки<br>';
}
      

function clearBD()
{
    $mysqli = createBD('wer');
    $massClear = [
            'DELETE FROM manager WHERE id > 0',
            'DELETE FROM auth_assignment WHERE user_id > 0',
            'DELETE FROM user WHERE id>0',
            'DELETE FROM history_zakaz WHERE id>0',
            'DELETE FROM master_vs_zakaz WHERE id>0',
            'DELETE FROM master_work_navik WHERE id>0',
            'DELETE FROM zakaz  WHERE id>0' ,
            'DELETE FROM klient_vs_zakaz  WHERE id>0',
            'DELETE FROM klient WHERE id>0',
            'DELETE FROM master  WHERE id>0',
            'ALTER TABLE user AUTO_INCREMENT=0',
            'ALTER TABLE master_work_navik AUTO_INCREMENT=0',
            'ALTER TABLE master AUTO_INCREMENT=0',
            'ALTER TABLE zakaz AUTO_INCREMENT=0',
            'ALTER TABLE klient_vs_zakaz AUTO_INCREMENT=0',
            'ALTER TABLE master_vs_zakaz AUTO_INCREMENT=0',
            'ALTER TABLE history_zakaz AUTO_INCREMENT=0',
            'ALTER TABLE klient AUTO_INCREMENT=0',
            'ALTER TABLE manager AUTO_INCREMENT=0'
        ];
    foreach ($massClear as $query) {
        if ($mysqli->query($query)) { }
        else { 
            echo '<br>', $mysqli->error; 
            $mysqli->close();
            exit();
        }
    }
    $mysqli->close();
    echo '<br> очистка новой базы выполнена<br>';
}

function createBD($param)
{
    if ($param == 'old') {
        $mysqli_old = new mysqli('localhost', 'root', '', 'er');  
        $mysqli_old->set_charset("utf8");
        if ($mysqli_old->connect_errno) {         
            echo '<br>Соединение с базой old невозможно';
            exit();
        } else {         
            echo '<br>old соединение установленно'; 
            return $mysqli_old;
        }
    } else {
        $mysqli = new mysqli('localhost', 'root', '', 'admin_basemaster1');                 
        $mysqli->set_charset("utf8");
        if ($mysqli->connect_errno) { 
            echo '<br>Соединение с базой невозможно';
            exit();
        } else { 
            echo '<br>соединение установленно';
            return $mysqli;
        }        
    }
}
/*
function proverka($mass)
{
    foreach ($mass as $one){
        foreach($one as $elem) {            
            $elem = str_replace(['"', "'"], '', substr ($elem, 0));
        }
    }
    return $mass;
}*/

function debugArray($array)
{
    echo '<div><pre>' . print_r($array, true) . '</pre></div>';
}
