<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models;

/**
 * Хранит настройки директорий
 * @author Gradinas
 */
class FileManager {
    
    const FILES = 'files';                                                      //  папка для хранения файлов
    
    const ADDRESS_CHATS = 'chats';                                              //  подпапка чатов
    const ADDRESS_ORDERS = 'orders';                                            //  подпапка заявок
    const ADDRESS_USERS = 'users';                                              //  подпапка пользователей
    const ADDRESS_SYSTEM = 'system';                                            //  системная подпапка
    
}
