var chat = null;
var idChat = null;
var setName = false;
  let HOST_NAME = "127.0.0.1";
//  let HOST_NAME = "expertpech.ru";
//  let HOST_NAME = "gradinas.ru";
let pathChatImage = "/images/chat/";

let OP_STATUS_MESSAGE = 101;
let OP_INPUT_MESSAGE = 102;
let OP_OUTPUT_MESSAGE = 103;
let OP_SET_USER_NAME = 104;
let OP_LIST_USERS = 105;
let OP_CREATE_NEW_CHAT = 106;
let OP_WRITEN = 107;
let OP_SYSTEM = 108;

let MESSAGE_SEND = 'send';
let MESSAGE_DELIVERED = 'delivered';
let MESSAGE_READED = 'readed';

let STATUS_ACCEPT = 1;
let STATUS_ERROR = 0;
let NULL_MESSAGE = 1008;

let MESSAGE_ALL = 1010;

let WS_CONNECTING = 0;
let WS_OPEN = 1;
let WS_CLOSING = 2;
let WS_CLOSED = 3;

let BLACK_LIST_ACTIVE = true;
let BLACK_LIST_BLOCKED = false;

var blockScroll = true;                                                        //  блокирует лишние запросы истории чата до момента загрузки и отображения новых данных

var lastTextSize = 0;

var schet = 0;   

var massMessages = [];

function createDialog(data = ""){
    return '<div id="dialog"><div id="dialog_body">' + data + '</div></div>';
}

$(window).on("load", function(){
    $("#w2").find('a[href="/chat/index"]').append('<div id="count_message" class="alarm-head">'
            + '<font id="text_count" style="text-align: center; vertical-align: middle;">0</font></div>');  
    
    if (window.location.href.indexOf('chat/index') !== -1) {
        $("#w2").find("#text_count").html("0");
        $("#w2").find(".alarm-head").css("width", "0px");
        $("#w2").find(".alarm-head").css("height", "0px");
        $("#w2").find(".alarm-head").css("visibility", "hidden");  
    }
});


function delet(element){
    $(element).parent().remove();
    if ($('#sp1-content').children().length == 0) $('#openChat')[0].style.backgroundColor = "#286090";
}

function createBlock(message){
    return '<div class="sp1-message">' + message 
            + '<button class="sp1-message-but" onclick="delet(this)"><b>X</b></button>';
}

function userWrite(){
    var newText = $("#editor").val();
   // console.log(newText.length);
    if (newText.length === 0) {  
        lastTextSize = newText.length;
   //     console.log("write-false");
        chat.send(JSON.stringify({"action" : "userWrite", "id_chat" : idChat, "write" : false}));        
    } else if (lastTextSize < newText.length) {       
        if ((newText.length - lastTextSize) > 10 ) {
            lastTextSize = newText.length;
       //     console.log("write-true");
            chat.send(JSON.stringify({"action" : "userWrite", "id_chat" : idChat, "write" : true}));
        }
    }         
}

/* 
 * Создает блок сообщения и добавляет его в чат
 */
function createMessage(elem, id_user){     
    var div;
    var parent = '';
    var file = '';
    if (elem.parent_id != undefined & elem.parent_id != null) {
        parent = '<div class="parent">'
                + $(".message-table").find("div[id="+ elem.parent_id +"]").find(".message").html()
                + '</div>';
    }
    
    if (elem.file != undefined & elem.file != null) {
        var str = elem.file.substr(-3);        
        switch (str){
            case 'avi': str = '/images/avi.png';
                break;
            case 'doc': str = '/images/doc.png';
                break;
            case 'exe': str = '/images/exe.png';
                break;
            case 'iso': str = '/images/iso.png';
                break;
            case 'mp3': str = '/images/mp3.png';
                break;
            case 'mp4': str = '/images/mp4.png';
                break;
            case 'pdf': str = '/images/mp3.png';
                break;
            case 'txt': str = '/images/txt.png';
                break;
            case 'xls': str = '/images/xls.png';
                break;
            case 'zip': str = '/images/zip.png';
                break;
            case 'png': 
            case 'jpg': str = pathChatImage + elem.id_chat + '/' + elem.file;
                break;  
            default: str = '/images/file.png';
                break;
        }   
        file = '<br><a href="' + pathChatImage + elem.id_chat + '/' + elem.file 
                    +'" target="_blank"><img src="' + str + '" style="margin: 5px; max-width: 250px;"></img></a>';
    }
    
    if (id_user == elem.id_user) {
        var src;        
        switch (elem.status_message) {
            case MESSAGE_SEND: src = '/images/send.png';
                break;
            case MESSAGE_DELIVERED: src = '/images/delivered.png';
                break;
            case MESSAGE_READED: src = '/images/readed.png';
                break;
        }
       
        div = '<div id="' + elem.id + '" class="message-left"  onclick="chooseMessage(this)">'+ parent +'<font class="message">' + elem.message 
            + '</font><br><font class="time">'+ elem.time + '</font><img style="margin-left: 5px;" src="' + src + '"></img>' + file + '</div>';
    } else {
        div = '<div id="' + elem.id + '" class="message-right" onclick="chooseMessage(this)">'
            + parent + '<font class="message"><b><i>' + elem.autor + ': </i></b>' + elem.message + '</font>'
            + '<br><font class="time">'+ elem.time + '</font>' + file + '</div>';
    }
    return div;
}



function chooseMessage(div){  
    
    $('.message-right').filter(function (index, elem){
        return elem.style.backgroundColor == "grey" & elem.id != div.id;
    }).css("background-color", "#993300");
    $('.message-left').filter(function (index, elem){
        return elem.style.backgroundColor == "grey" & elem.id != div.id;
    }).css("background-color", "#993300");    
   
    if (div.style.backgroundColor != "grey")  {
        div.style.backgroundColor = "grey";
    } else {
        div.style.backgroundColor = "#993300";
    }
}

/*
 * Создает заглавие чата в виде даты
 */
function createDate(date, loadingDate = false){
   /* if (loadingDate) $(".message-table").find("td").first().append('<div class="date-message-container">' 
            + date + '</div>');
    else */ 
    return '<div class="date-message-container">' + date + '</div>';
  /*  else $(".message-container").append('<div class="date-message-container">' 
            + date + '</div>');*/
}

function createStr(div, flag = false){
    if (flag)
    return '<tr><td align="center">' + div + '</td></tr>';
    
    return '<tr><td>' + div + '</td></tr>';
}

function addUsersToMass(elem){
    var massiv = [];
    $(elem).find("input:checkbox:checked").each(function(index, elem){
        massiv.push($(elem).attr('id'));        
    });
    return massiv;
}

/*
 * Создает и заполняет начальный чат сообщениями
 * @param {type} data
 * @returns {undefined}
 */
function initializationChat(data){                                                      //  при первом запуске наполняет окно сообщениями из истории
   
    $(".message-container").append('<table class="message-table"></table>'); 
 
    if (!data.chat.length > 0) {
        var date = new Date();        
        $(".message-table").append(createStr(createDate(date.getFullYear() + '-' + date.getMonth() + '-' + date.getDate()), true)); 
        return;
    }
 
    $(".message-table").append(createStr(createDate(data.chat[0].date), true));                                            //  очищаем контейнер сообщений
                                        
    $(data.chat).each(function(index, elem){                                    //  пробегаемся по каждому сообщению 
        $(".message-table").append(createStr(createMessage(elem, data.id)));           //  конструктор сообщений
    });     
    $(".message-container")[0].scrollTop = $(".message-container")[0].scrollHeight;         //  прокручиваем историю чата до последнего сообщения
}


function addUserToChat(){
    var massUsers = addUsersToMass($("#search_result"));
    
    $("#dialog").remove();
 
    var data = { "id" : idChat, "users" : massUsers };
    $.ajax({
        url: "add-user",
        type: "post",
        dataType: "json",
        data: data,
        success: function (data){
           // console.log(data);
            if (data.status == STATUS_ACCEPT) createUsersOfChatDialog();
            else alert(data.s_message);
        }
    });
}

function createFindUserDialog(newChat = true){ 
    
    var processFunc = 'createNewChat()';
    var butName = 'Создать';
    var butTitle = 'Создать новый чат';
    var chatName = '<input type="text" class="chat-input" name="name_chat" placeholder="Название нового чата"'
            +' id="name_chat" maxlength="30" size="15" title="Это название отобразится у всех пользователей чата">';
    var dialogTitle = "Новый чат";
    
    if (!newChat) {
        processFunc = "addUserToChat()";
        butName = "Добавить";
        butTitle = "Добавить выбраных пользователей в чат";
        chatName = "";
        dialogTitle = "Поиск пользователей";
    } 
    
    $("body").append(createDialog('<div  style="text-size: 11pt; color: #dd591c;" >' 
        +'<table id="search_table" >'
            +'<tr>'
                +'<td class="my-td"><input type="text" class="chat-input" name="familiya" placeholder="Фамилия" id="search_editor_familiya" size="14"></td>'
                +'<td class="my-td"><input type="text" class="chat-input" name="imya" placeholder="Имя" id="search_editor_imya" size="14"></td>'
                +'<td class="my-td"><input type="text" class="chat-input" name="otchestvo" placeholder="Отчество" id="search_editor_otchestvo" size="14"></td>'
            +'</tr>'
            +'<tr> '
                +'<td class="my-td"><input type="text" class="chat-input" name="username" placeholder="Имя пользователя" id="search_editor_username" size="14" title="Логин"></td>'
                +'<td class="my-td"><input type="text" class="chat-input" name="phone" placeholder="Телефон" id="search_editor_phone" size="14" title="Указывать без кода страны"></td>'
                +'<td class="my-td"><button type="button" onclick="searchUser()" class="btn btn-block" title="Поиск пользователя">Поиск</button></td>'
                
            +'</tr>'
            +'<tr>'
                +'<td class="my-td" colspan="2">'+ chatName +'</td> '
                +'<td class="my-td"><button type="button" class="btn btn-block" onclick="'+ processFunc +'" title="'+ butTitle +'">'+ butName +'</button></td>'
            +'</tr>'
        +'</table>'        
        +'<div id="search_result" style="max-height: 320px; overflow-y: scroll; margin-top: 10px; width: 100%; height: auto;"></div>'
        + '</div>'));

    $('#dialog').dialog({          
        modal: true,
        resizeble: false,
        open: function (){
            console.log($('.ui-dialog-titlebar-close').html('&times;'));
            $('.ui-dialog-titlebar-close').css("padding", "0px");
            $('.ui-dialog-titlebar-close').attr("title", "Закрыть");
        },
        close: function (){
            $('#dialog').remove();
        },
        title: dialogTitle,
        autoOpen: true,
        width: 500,        
        height: 520
    }); 
}

function setClickListenerToSearchUser(){    
    
    $("#dialog_body").find(".search-user").on("click", function(){
        if ($(this).find("input").prop("checked")) $(this).find("input").prop("checked", false);
        else $(this).find("input").prop("checked", true);
    });
}

function createUser(id_user, fio, username, showStatusUser = false, statusUser = true, showStatusConnect = false, connected = false){
    
    var div = '';
    var background = '';
    var str = '';
    var connect = '';
    if (showStatusUser) {
        switch (statusUser){
            case BLACK_LIST_ACTIVE : str = " активен";
                break;
            case BLACK_LIST_BLOCKED : 
                background = 'background-color: grey;';
                str = " заблокирован";
                break;
        }      
    }
    
    if (showStatusConnect) {
        if (connected) connect = '<div class="connected-true"></div>';
        else connect = '<div class="connected-false"></div>';
    }
    
    return  '<table id="' + id_user + '" cols="3" rows="2" class="search-user" style="'+ background 
                        + ' border-radius: 10px; width: 97%;">'
                +'<tr>'  
                    +'<td class="my-td" rowspan="2">'
                        + '<input type="checkbox" id="' + id_user + '">'
                    +'</td>'
                    +'<td class="my-td" style="width: 65%; padding-top: 10px;">'
                        + '<div style="font-size: 11pt; font-style: italic;">'+ fio + '</div>'
                    +'</td>'
                    +'<td class="my-td">' + connect
                    +'</td>'
                +'</tr>'
                +'<tr>'
                    +'<td class="my-td">'
                        + '<div style="font-size: 11pt; font-weight: bold;">' + username + '</div>'
                    +'</td>'
                    +'<td class="my-td">'
                        + '<font style="font-size: 10pt; font-style: italic;">Статус:</font>'
                        + '<font id="status" style="font-size: 10pt; font-weight: bold;">' + str + '</font>'
                    +'</td>'
                +'</tr>'
            +'</table>';
}

function createUsersOfChatDialog(){
    
    $("body").append(createDialog());    
    $('#dialog').dialog({
     //   "buttons": [{ text: "OK", click: console.log("event1")}],        
        modal: true,
        resizeble: false,
        open: function (){
            $('.ui-dialog-titlebar-close').html('&times;');
            $('.ui-dialog-titlebar-close').css("padding", "0px");
            $('.ui-dialog-titlebar-close').attr("title", "Закрыть");         
        },
        close: function (){
            $("#dialog").remove();
        },
        buttons : [
            { text: "Добавить пользователя", 
                click: function (){
                    $('#dialog').dialog("close");
                    createFindUserDialog(false);
                } 
            },
            { text: "Удалить пользователей", 
                click: function (){
                    var massUsers = addUsersToMass($("#dialog_body"));
                    if (massUsers.length == 0) { 
                        alert("Не выбран ни одинпользователь");
                        return;
                    };
                    $.ajax({
                        url: "delete-user-from-chat",
                        type: "post",
                        dataType: "json",
                        data: { "id" : idChat, "users" : massUsers },
                        success: function (data){
                      //      console.log(data);
                            if (data.status == STATUS_ACCEPT) {
                                $(massUsers).each(function (i, k){                                    
                                    $("#dialog_body").find(".search-user").each(function (index, elem){
                                        if (k == elem.id) $(elem).remove();
                                    });                                        
                                });                                      
                            } else alert(data.s_message);
                        },
                        error: function (data){ console.log(data); }
                    });
                } 
            },
            { text: "Заблокировать", 
                click: function (){
                    var massUsers = addUsersToMass($("#dialog_body"));
                    if (massUsers.length > 0) {
                        $.ajax({
                            url: "block-users",
                            type: "post",
                            dataType: "json",
                            data: { "users" : massUsers },
                            success: function (data){
                             //   console.log(data);
                                if (data.status == STATUS_ACCEPT) {
                                    $(massUsers).each(function (i, k){                                    
                                        $("#dialog_body").find(".search-user").each(function (index, elem){
                                            if (k == elem.id) {
                                                $(elem).css("background-color", "grey");
                                                $(elem).find("#status").html(" заблокирован");
                                            }
                                        });                                        
                                    });                                      
                                }
                            },
                            error: function (data){ console.log(data); }
                        });
                    }
                } 
            },
            { text: "Разблокировать", 
                click: function (){                 
                    var massUsers = addUsersToMass($("#dialog_body"));
                    if (massUsers.length > 0) {
                        $.ajax({
                            url: "unlock-users",
                            type: "post",
                            dataType: "json",
                            data: { "users" : massUsers },
                            success: function (data){
                          //    console.log(data);
                                if (data.status == STATUS_ACCEPT) {
                                    $(massUsers).each(function (i, k){
                                        $("#dialog_body").find(".search-user").each(function (index, elem){
                                            if (k == elem.id) {                                            
                                                $(elem).css("background-color", "white");
                                                $(elem).find("#status").html(" активен");
                                            }
                                        });                                        
                                    });                                      
                                }
                            },
                            error: function (data){ console.log(data); }
                        });
                    }
                } 
            }
        ],
        title: "Список участников чата",
        autoOpen: true,
        width: 500,
        height: 520
    }); 
    if (securityWebSocket()) {
        console.log("SEND QUERY LIST USERS");
        chat.send(JSON.stringify({ "action" : "listChatUsers", "id" : idChat })); 
    } else {
        alert("WS соединение закрыто, перезагрузите страницу");
    }
 /*   $.ajax({
        url: "list-chat-users",
        type: "post",
        dataType: "json",
        data: { "id" : idChat },
        success: function (data){
            if (data.status == STATUS_ACCEPT) {
                var status;
                $(data.chat_users).each(function (index, elem){
                    status = true;
                    if (data.id != elem.user_id) {
                        $(data.black_list).each(function (i, k){
                            if (k.locked == elem.user_id) status = false;
                        });
                        $("#dialog_body").append(createUser(elem.user_id, elem.fio, elem.username, true, status));
                    }   
                });
                setClickListenerToSearchUser();
            } else console.log(data.s_message);
        },
        error: function (data){ console.log(data); }
    });   */ 
}

function createBlackListDialog(){    
    console.log("createBlackListDialog()");
    $("body").append(createDialog());
    $('#dialog').dialog({     
        modal: true,
        resizeble: false,
        open: function (){
            $('.ui-dialog-titlebar-close').html('&times;');
            $('.ui-dialog-titlebar-close').css("padding", "0px");
            $('.ui-dialog-titlebar-close').attr("title", "Закрыть");         
        },
        close: function (){
            $('#dialog').remove();
        },
        buttons : [
            { text: "Разблокировать", 
                click: function (){
                    var massUsers = addUsersToMass($("#dialog_body"));
                    if (massUsers.length > 0) {
                        $.ajax({
                            url: "unlock-users",
                            type: "post",
                            dataType: "json",
                            data: { "users" : massUsers },
                            success: function (data){
                                console.log(data);
                                if (data.status == STATUS_ACCEPT) {
                                    $("#dialog_body").empty();                          
                                }
                            },
                            error: function (data){ console.log(data); }
                        });
                    }
                } 
            }
        ],
        title: "Черный список",
        autoOpen: true,
        width: 500,
        height: 520
    }); 
    
    $.ajax({
        url: "black-list-users",
        type: "post",
        dataType: "json",
        data: {},
        success: function (data){
            console.log(data);
            if (data.status == STATUS_ACCEPT) {
                $(data.users).each(function (index, elem){
                    $("#dialog_body").append(createUser(elem.user_id, elem.fio, elem.username, true, false));
                });
                setClickListenerToSearchUser();
            } else console.log(data.s_message);
        },
        error: function (data){ console.log(data); }
    });    
}


function showSearchUsers(users, myId){
    $(users).each(function(index, elem){
        if (elem.id != myId) $("#search_result").append(createUser(elem.id, elem.familiya + ' ' + elem.imya +' '+ elem.otchestvo, elem.username)); 
    }); 
    setClickListenerToSearchUser();
}

function searchUser(){
    var data = {};    
    $("#search_table").find("input").each(function(index, elem){
        data[elem.name] = elem.value.trim();        
    });
    //console.log($(data));
    
    $("#search_result").empty();
    $.ajax({
        url: "search-user", 
        type: "post",  
        dataType: "json",
        data: {"search" : data},                                             
        success: function(data){ 
            console.log("ИНТЕРЕСНО");
            console.log(data);
            if (data.status == STATUS_ACCEPT) showSearchUsers(data.users, data.id);
            else alert("Не найдено совпадений");
        },        
        error: function(error){}
    });  
}


function createNewChat(){
    
    var chat_name = ($("#name_chat").val()).trim();
    if (chat_name == "") {
        alert("Название чата не может быть пустым");
        $("#name_chat").focus();
        return;
    }    
    var mass = addUsersToMass($("#search_result"));
    
    if (mass.length == 0) {
        alert('Не выбран ни один пользователь');
        return;
    }
   // jQuery('#dialog').dialog("close");
    $("#dialog").remove();
    var data = {"action" : "createChat", "chat_name" : chat_name, "users" : mass};      
    
    if (securityWebSocket()) {
        chat.send(JSON.stringify(data));
    }
  
}


function exitChat(){
    $("body").append(createDialog());
    var name = $(".chat-user[id="+ idChat +"]").find("#chat_name").html();
    
    $("#dialog_body").append('Вы покидаете чат "' + name + '"!!!<br>' 
            + 'Для продолжения выберите пользователя которому передаете авторство над чатом.'
            + ' Если пользователь не указан и вы являетесь автором, система передаст авторство'
            + ' случайному пользователю. <br><div id="list"></div>');
    
    $("#dialog").dialog({          
        modal: true,
        resizeble: false,
        open: function (){
            console.log($('.ui-dialog-titlebar-close').html('&times;'));
            $('.ui-dialog-titlebar-close').css("padding", "0px");
            $('.ui-dialog-titlebar-close').attr("title", "Закрыть");
        },
        close: function (){
            $('#dialog').remove();
        },
        buttons: [{
                text: "Да",
                click: function (){                         
                    var mass = addUsersToMass($("#list"));
                    var id = 0;
                    if (mass.length != 0) id = mass[0];
                    var data = { "id" : idChat, "id_user" : id };
                    console.log(data);
                    $.ajax({
                        url: "exit-chat",
                        type: "post",
                        dataType: "json",
                        data: data,
                        success: function (data){
                            console.log(data);
                            if (data.status == STATUS_ACCEPT){
                                $(".chat-user[id="+ idChat +"]").remove();
                                $(".message-container").empty();                                
                            } else {
                                alert(data.s_message);
                            }
                            $("#dialog").dialog("close");
                        },
                        error: function (data){ console.log(data); }
                    });
                }
        }],
        title: "Выход из чата",
        autoOpen: true,
        width: 500,        
        height: "520"
    });
    
    $.ajax({
        url: "list-chat-users",
        type: "post",
        dataType: "json",
        data: { "id" : idChat },
        success: function (data){
            if (data.status == STATUS_ACCEPT) {
                var status;
                $(data.chat_users).each(function (index, elem){
                    status = true;
                    if (data.id != elem.user_id) {
                        $(data.black_list).each(function (i, k){
                            if (k.locked == elem.user_id) status = false;
                        });
                        $("#list").append(createUser(elem.user_id, elem.fio, elem.username, true, status));
                    }   
                });
                setClickListenerToSearchUser();
            } else console.log(data.s_message);
        },
        error: function (data){ console.log(data); }
    });
}


function deleteChat(){
    
    $("body").append(createDialog());
    var name = $(".chat-user[id="+ idChat +"]").find("#chat_name").html();
    
    $("#dialog_body").append('Вы удаляете чат "' + name + '"!!!<br><br>'
            + 'При создании нового чата с таким же набором пользователей, чат будет'
            + ' восстановлен из истории <br><br>Продолжить?');
    
    $("#dialog").dialog({          
        modal: true,
        resizeble: false,
        open: function (){
            console.log($('.ui-dialog-titlebar-close').html('&times;'));
            $('.ui-dialog-titlebar-close').css("padding", "0px");
            $('.ui-dialog-titlebar-close').attr("title", "Закрыть");
        },
        close: function (){
            $('#dialog').remove();
        },
        buttons: [{
                text: "Да",
                click: function (){
                    $.ajax({
                        url: "remove-chat",
                        type: "post",
                        dataType: "json",
                        data: { "id" : idChat },
                        success: function (data){
                            console.log(data);
                            if (data.status == STATUS_ACCEPT){
                                $(".chat-user[id="+ idChat +"]").remove();
                                $(".message-container").empty();                                
                            } else {
                                alert(data.s_message);
                            }
                            $("#dialog").dialog("close");
                        },
                        error: function (data){ console.log(data); }
                    });
                }
        }],
        title: "Удаление чата",
        autoOpen: true,
        width: 500,        
        height: "auto"
    });
}


function securityWebSocket(){
    
 /*   if (chat === undefined || chat === null) {
        createWebSocket();         
    }   */
    
    if (chat === undefined || chat === null) {
        createWebSocket();         
    }        
    switch (chat.readyState) {            
        case WS_CLOSING:
        case WS_CLOSED: createWebSocket();
            break;    
        case WS_OPEN: return true;
            break;  
        default : return false;
    }
   
  /*  if (chat.readyState === WS_CLOSING || chat.readyState === WS_CLOSED) {
        createWebSocket();        
    }*/
    
  /*  if (chat.readyState == WS_CONNECTING) {
        console.log("WS connecting...");       
      //  setTimeout(console.log('wait 2 second'), 2000);
        return true;
    }    */
    
 /*   if (chat.readyState === WS_OPEN) {   
        return true;
    }*/    
   
}

/*
 * Отправляет новое сообщение чата на сервер
 * @returns {undefined}
 */
function sendMessage(){ 
    if (securityWebSocket()) {   
        lastTextSize = 0;
        var message = $("#editor").val();    
        var right = $('.message-right').filter(function (index, elem){
            return elem.style.backgroundColor == "grey";
        }).css("background-color", "#993300");
        var left = $('.message-left').filter(function (index, elem){
            return elem.style.backgroundColor == "grey";
        }).css("background-color", "#993300");
     
        var dataWS = {"action" : "chat", "message" : message, "id_chat" : idChat};
        if (right.length > 0) {
            dataWS.parent_id = right[0].id;
        } else if (left.length > 0) {
            dataWS.parent_id = left[0].id;
        } 
    
        var file = $("#inputFile").prop("files")[0];
        if (file != undefined & file != null) {
            
            if (file.size > 30000000) {                                                     
                alert("Размер файла не должен превышать 30 Мб");   //  иначе покажем сообщение и завершим цикл
                return false;
            }           
            var dataArray = new FormData();
            dataArray.append("file", file);
            dataArray.append("id_chat", idChat);  
                      
            $.ajax({
                type: "post",
                data: dataArray,                                             //  отправим наш формдата
                url: "save-file",
                dataType: 'json',
                cache: false,                                               //  
                contentType: false,                                         //
                processData: false,                 
                xhr: function(){
                    var xhr = new window.XMLHttpRequest();		
                    xhr.upload.addEventListener("progress", function(evt){
                        if (evt.lengthComputable) {
                            $('#progressDiv').css("visibility", "visible");                                
                            $('#progress_line').css("width", (evt.loaded / evt.total * 100) + '%');
                        }
                    }, true);                   
                    return xhr;
                },
                success: function(data) {
                    console.log(data);
                    $('#progressDiv').css("visibility", "hidden");
                    if (data.status == STATUS_ACCEPT){
                        dataWS.file = data.file;
                        chat.send(JSON.stringify(dataWS));
                    } else console.log(data); 
                },
                error: function(data) {
                    console.log(data);
                    $('#progressDiv').css("visibility", "hidden");
                } 
            }); 
        } else { 
            chat.send(JSON.stringify(dataWS)); 
        }   
        var parent = $("#inputFile").parent();
        $("#inputFile").remove();
        $(parent).append('<input id="inputFile" type="file" style="display: none;">');
        $("#editor").val("");
    } else {
        alert("WS соединение закрыто, перезагрузите страницу");
    }
}

/*
 * Выводит подгруженные данные чата (предыдущая дата). Сообщения придут отсортированные 
 * в обратной временной последовательности, т.е. ["0" => 23:59,
 *                                                "1" => 22:59,
 *                                                "n" => 00:00]
 * @returns {undefined}
 */
function loadingChat(data){                                                     //  создаем заглавие чата в виде даты
    var table = $(".message-table")[0];
    $(data.chat).each(function(index, elem){                                    //  пробегаемся по каждому сообщению 
        $(".message-table").prepend(createStr(createMessage(elem, data.id)));           //  конструктор сообщений
     /*   var row = table.insertRow(0);
        var cell = row.insertCell();
        $(cell).append(createMessage(elem, data.id));
        console.log(createStr(createMessage(elem, data.id)));*/
    }); 
    $(".message-table").prepend(createStr(createDate(data.chat[0].date), true));
}

/*
 * 
 * @param {type} target
 * @returns {undefined}
 */
function messageContainerScroll(target){
 //   console.log($(target)[0].scrollTop);
    
    if ($(target)[0].scrollTop == 0 & blockScroll) {
        console.log("idChat : " + idChat);
        blockScroll = false;
        console.log("в блоке");
        getHistoryChat($(".contacts").children("#" + idChat)[0], 
            $(".message-container").find(".date-message-container").first().html(), true);
    }
}

/*
function downloadListUsers(){
    
    $("#container_list_users").empty();    
    if (idChat == null) {
        alert("Не выбран ни один чат");
        return;
    }    
    chat.send(JSON.stringify({ "action" : "list-chat-users", "id" : idChat }));    
  /* $.ajax({
        url: "list-chat-users",
        type: "post",
        dataType: "json",
        data: {"id" : idChat},
        success: function(data){
            console.log("ОТВЕТ СЕРВЕРА");
            console.log(data);
            if (data.status == STATUS_ACCEPT) {
                
                $(data.chat_users).each(function (index, elem){
                    var status = BLACK_LIST_ACTIVE;
                    $(data.black_list).each(function (i, k){
                        if (k.locked == elem.user_id) status = BLACK_LIST_BLOCKED;
                    });
                    if (elem.user_id != data.id) 
                        $("#container_list_users").append(createUser(elem.user_id, elem.fio, elem.username, true, status));
                });  
                $(".search-user").on("click", function(){
                    if ($(this).find("input").prop("checked")) $(this).find("input").prop("checked", false);
                    else $(this).find("input").prop("checked", true);
                });
            } else $("#container_list_users").append(data.s_message);           
        },
        error: function(error){
            alert(error);
        }
    });
}*/




/*
 * Загружает данные чата последней доступной даты или подгружает данные указанной даты
 * @param {type} event target
 * @returns {undefined}
 */
function getHistoryChat(target, date = null, loadingData = false){
  //  console.log(arguments);    
  
    $("#editor").val("");
    if (idChat != null) console.log($(".chat-user").filter(function (index, elem){
        return elem.id == idChat;
    }).css("background-color", "#d66620"));
    idChat = target.id;
    target.style.backgroundColor = "#993300";
    
    var dataSend = { "id" : target.id };
    if (date !== null) dataSend.date = date; 
    
    if (loadingData == false) $(".message-container").empty();    
  //  console.log(dataSend);
    $.ajax({    
        url: "history-message", 
        type: "post",  
        dataType: "json",
        data: dataSend,                                             
        success: function(data){ 
         //   console.log("В данный момент");
         //   console.log(data);
            blockScroll = true;
            if (data.status == STATUS_ACCEPT) {                
                if (data.chat.length == 0) {
                    console.log("Чат пуст");                  
                }
                if (loadingData) loadingChat(data);                    
                else {                    
                    $(".chat-user").each(function (index, elem){
                        if (elem.id == idChat) {
                            $(elem).find("#count_message").css("visibility", "hidden"); 
                            $(elem).find("#text_count").html("0");      
                        }
                    });
                    initializationChat(data);
                    if (securityWebSocket()) {
                        chat.send(JSON.stringify({"action" : "status", "id" : "0", "id_chat" : idChat, "s_code" : MESSAGE_ALL}));
                    } else {
                        alert("WS соединение закрыто, перезагрузите страницу");
                    }
                }
            } else { console.log(data.s_message); }
        },        
        error: function(error){ console.log("Error!", error); }
    });
}

/*
 * Создает новый WS канал связи и обрабатывает события этого канала
 * @returns {undefined}
 */
function createWebSocket(){
    chat = new WebSocket('ws://' + HOST_NAME + ':25555');
    
    chat.onopen = function(e) {                                                 //  событие возникает при успешном открытии канала
        console.log("Соединение WS -> ok");  
      //  if (setName) {
            chat.send(JSON.stringify({"action" : "setName", "name" : userName}));
            console.log("WS name -> ok");  
            massMessages.forEach(function (item, i, arr){
                chat.send(JSON.stringify(item));
            });
            massMessages = [];
     //   }
    };

    chat.onerror = function(e) {        
        alert('Ошибка соединения с сервером, перезагрузите страницу. Если сообщение повторяется более 3 раз сообщите программисту');      
    }; 
    
    chat.onmessage = function(e) {  
        
        var response = JSON.parse(e.data);
       console.log(response); 
        if (response.operation == "name") {
            console.log(response);
            return;
        }
        
        if (window.location.href.indexOf('chat/index') === -1) {
            if (response.operation == OP_INPUT_MESSAGE) {
                if ($("#sp1-content").length > 0 & response.id_autor == "0") {
                    $("#sp1-content").append(createBlock(response.message));
                    $("#openChat").css("background-color", "red");                
                    $("#sp1-content")[0].scrollTop = $("#sp1-content")[0].scrollHeight;
                }                                
                
                $("#w2").find("#text_count").html(Number($("#w2").find("#text_count").html()) + 1);
                $("#w2").find(".alarm-head").css("width", "24px");
                $("#w2").find(".alarm-head").css("height", "24px");
                $("#w2").find(".alarm-head").css("visibility", "visible"); 
                
                if (securityWebSocket()) {
                    chat.send(JSON.stringify({
                        "action" : "status",                        
                        "id_chat" : response.id_chat, 
                        "id" : response.id, 
                        "s_code" : MESSAGE_DELIVERED
                    }));      
                } else {
                    alert("WS соединение закрыто, перезагрузите страницу");
                }
            }            
            return;
        }
        
   //     console.log(response);
      //  if (response.status !== STATUS_ACCEPT) {
           // console.log("status = false, s_message = " + response.s_message);
       //    console.log(response);
  //          return;
   //     }

        if (response.pong !== undefined) {
           // console.log("status = false, s_message = " + response.s_message);
     //       console.log(response);
     //       return;
        }
console.log(response);
        switch (response.operation){
            case OP_STATUS_MESSAGE: 
       //         console.log('OP_STATUS_MESSAGE');
       //         console.log(response);
              //  $(response).each(function(i, one){
                    if (idChat !== response.id_chat) {                        //  проверяем, что статус поменялся у сообщения из открытого чата иначе сброс
                        $(".contacts").children(".chat-user").each(function(index, elem){               
                            if (elem.id == response.id_chat) {
                                $(elem).find("#count_message").css("visibility", "visible");   
                                var count = Number($(elem).find("#text_count").html());                 
                                if (count !== 0 & count !== NaN) count += 1;
                                else count = 1;
                                $(elem).find("#text_count").html(count);
                            }
                        });
                    } else {
                        var imgName = "";
                        $(".message-container :first").find(".message-left").each(function(index, elem){                      
                            if (elem.id == response.id) { 
                                switch (response.s_code) {
                                    case MESSAGE_SEND: imgName = "/images/send.png";
                                        break;
                                    case MESSAGE_DELIVERED: imgName = "/images/delivered.png";
                                        break;
                                    case MESSAGE_READED: imgName = "/images/readed.png";
                                        break;
                                    default: alert("картинка не найдена");
                                }                        
                                $(elem).find("img:first").attr("src", imgName);
                     //           console.log($(elem).find("img"));
                      //          console.log(elem);
                            }
                        });
                    }
            //    });               
                
                break;
            case OP_INPUT_MESSAGE:  
         //       console.log('OP_INPUT_MESSAGE');
         //       console.log(response);
                response.status_message = response.s_code;
                
                $(".message-table").find('.write[id='+ response.autor +']').closest("tr").remove();
                
                if (idChat !== response.id_chat){
                    $(".contacts").children(".chat-user").each(function(index, elem){               
                        if (elem.id == response.id_chat) {
                            $(elem).find("#count_message").css("visibility", "visible");   
                            var count = Number($(elem).find("#text_count").html());                 
                            if (count !== 0 & count !== NaN) count += 1;
                            else count = 1;
                            $(elem).find("#text_count").html(count);
                        }
                    });
                    var status = MESSAGE_DELIVERED;                    
                } else {                    //  проверяем, что новое сообщение из открытого чата, иначе стоп
                    $(".message-table").append(createStr(createMessage(response, response.id_autor)));
                    $(".message-container")[0].scrollTop = $(".message-container")[0].scrollHeight;
                    var status = MESSAGE_READED;                              
                }
          
                $(".chat-user").each(function(index, elem){               
                    if (elem.id == response.id_chat) {
                        $(elem).find("#last_message").html(response.autor + ": <i>" + response.message + "</i>");   
                        $(elem).find("#last_date").html(response.time); 
                    }
                });
              
                if (response.id_user != response.id_autor) {
                    if (securityWebSocket()) {
                        chat.send(JSON.stringify({
                            "action" : "status",                        
                            "id_chat" : response.id_chat, 
                            "id" : response.id, 
                            "s_code" : status
                        }));      
                    } else {
                        alert("WS соединение закрыто, перезагрузите страницу");
                    }
                }
                break;
         /*   case OP_OUTPUT_MESSAGE:                 
                break;*/
            case OP_SET_USER_NAME: 
                if (response.status === STATUS_ACCEPT) setName = false;
                break;
            case OP_LIST_USERS:
         //       console.log('OP_LIST_USERS');
         //       console.log(response);
                if (response.status === STATUS_ACCEPT) {
                   
                    var status;
                    $(response.chat_users).each(function (index, elem){
                        status = true;
                        if (response.id != elem.user_id) {
                            $(response.black_list).each(function (i, k){
                                if (k.locked == elem.user_id) status = false;
                            });
                            $("#dialog_body").append(createUser(elem.user_id, elem.fio, elem.username, true, status, true, elem.connected));
                        }   
                    });
                    setClickListenerToSearchUser();
                    
                } else $("#container_list_users").append(response.s_message);  
                break;
            case OP_CREATE_NEW_CHAT: 
                if (response.status == STATUS_ACCEPT) {
                    var autor = '';
                    if (response.id_autor == response.id_user) {
                        autor = '&copy;';
                    }                    
                    $("#contacts").prepend(
                            '<table id="'+ response.id_chat +'" rows="2" cols="4" class="chat-user" onclick="getHistoryChat(this)" >\n\
                                <tr><td class="user-td" id="chat_name" colspan="2" style="font-size: 11pt; width: auto;"><b>'+ response.message  
                            +'</b></td><td class="user-td" id="chat_autor" style="width: 20px; padding-left: 5px;">'+ autor 
                            +'</td><td class="user-td" id="chat_alarm" style="width: 40px;"><div id="count_message" class="alarm">\n\
                            <font id="text_count" style="text-align: center; vertical-align: middle;"></font></div></td></tr><tr>\n\
                            <td colspan="2" class="user-td" id="last_message" style="padding-bottom: 10px; padding-top: 0px; font-size: 9pt;">'
                            +'</td><td class="user-td" id="last_date" colspan="2" style="text-overflow: clip; padding-left: 4px;\n\
                             padding-top: 0px; padding-bottom: 10px; padding-right: 5px; font-size: 9pt; font-style: italic;">'
                            +'</td></tr></table>');
                    getHistoryChat($("#contacts").children()[0]);            
                } 
                break;
            case OP_WRITEN:
                if (response.id_chat == idChat) {
                    if (response.s_code) {
                        if ($(".message-table").find(".write[id="+ response.autor +"]").length > 0)  
                                $(".message-table").find(".write[id="+ response.autor +"]").remove();
                        $(".message-table").append(createStr('<div id="'+ response.autor +'" class="write">'+ response.autor +' печатает</div>'));
                        $(".message-container")[0].scrollTop = $(".message-container")[0].scrollHeight;
                    } else {
                        $(".message-table").find(".write[id="+ response.autor +"]").remove();
                    }  
                }                 
                break;
            default: console.log(response);
        }
        
    /*    if (response.type && response.type == 'chat') {
            $('#sp1-content').append(createBlock('<b>' + response.from 
                    + '</b>: ' + response.message ));
            $('#sp1-content').scrollTop = $('#sp1-content').height;
            if ($('#sp1-content').children().length > 0) $('#openChat')[0].style.backgroundColor = "red";
        } */
    };
}


