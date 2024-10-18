console.log("MESSENGER");
var chat = null;
var idChat = null;
var setName = false;
let HOST_NAME = "localhost";
//let HOST_NAME = "expertpech.ru";
//let HOST_NAME = "gradinas.ru";
let pathChatImage = "/files/chats/";
let protocol = "ws";
let port = ":25555";
let adress = "";

let ERROR_USER_NAME = 1001;
let ERROR_WRITE_BASE = 1002;
let ERROR_SEND_MESSAGE = 1003;
let ERROR_SEND_PARAMETR = 1004;

let OP_STATUS_MESSAGE = 101;
let OP_INPUT_MESSAGE = 102;
let OP_NEW_MESSAGE = 103;
//let OP_OUTPUT_MESSAGE = 103;
let OP_SET_USER_NAME = 104;
let OP_LIST_USERS = 105;
let OP_CREATE_NEW_CHAT = 106;
let OP_WRITEN = 107;
let OP_SYSTEM = 108;
let OP_ERROR_NAME = 109;
let OP_SEARCH_USER = 110;
let OP_GET_CHATS = 111;
let OP_GET_HISTORY_MESSAGE = 112;
let OP_EXIT_CHAT = 113;
let OP_REMOVE_USER = 114;
let OP_ADD_USER = 115;
let OP_REMOVE_CHAT = 116;
let OP_BLOCK_USERS = 117;
let OP_UNLOOCK_USERS = 118;
let OP_BLACK_LIST_USERS = 119;
let OP_HAVE_MESSAGE = 121;

let MESSAGE_SEND = 'send';
let MESSAGE_DELIVERED = 'delivered';
let MESSAGE_READED = 'readed';

let STATUS_ACCEPT = 1;
let STATUS_ERROR = 0;
let NULL_MESSAGES = 1008;

let MESSAGE_ALL = 1010;

let WS_CONNECTING = 0;
let WS_OPEN = 1;
let WS_CLOSING = 2;
let WS_CLOSED = 3;

let CHAT_ACTIVE = 'active';
let CHAT_DIACTIVATED = 'diactivated';
let CHAT_DELETED = 'deleted';

let BLACK_LIST_ACTIVE = true;
let BLACK_LIST_BLOCKED = false;

var blockScroll = true;                                                         //  блокирует лишние запросы истории чата до момента загрузки и отображения новых данных
var flagShowMessenger = false;

var lastTextSize = 0;

var schet = 0;

var massMessages = [];

/**
 * Предварительный инициализатор
 */

/*function initUserName() {
    var str = $("#writen").val();
    if (str.localeCompare("") == 0) return;
    userName = str;
    console.log("userName => " + userName);
}*/
$(window).on("load", function() {
/*function startWSmy(){
 /*   if (userName == null) {
        alert("Введите имя пользователя")
        return;
    }*/

    $('#messengerBody').on('show.bs.collapse', function () {
        //  console.log("EVENT1");
        $("#openChat").css("padding", "5px");
        $("#head_alarm").css("display", "none");

    //    if (securityWebSocket() && $("#chats").children().length == 0){
            //   console.log("EVENT1801");
    //        chat.send(JSON.stringify({"action": "getChats"}));
  //      }
    });

    $('#messengerBody').on('shown.bs.collapse', function () {
        //     console.log("EVENT2");
    });
    $('#messengerBody').on('hide.bs.collapse', function () {
        //   console.log("EVENT3");
        $("#openChat").css("padding", "20px");
    });
    $('#messengerBody').on('hidden.bs.collapse', function () {

        //      console.log("EVENT4");
    });

    /* $("#openChat").on("click", function(){
         if ($(this).css("padding") == "20px") $(this).css("padding", "5px");
         else $(this).css("padding", "20px");
         if (flagShowMessenger == true) {
             flagShowMessenger = false;
             $("#head_alarm").css("display", "none");
             $(".chats").children(".chat-user").each(function(index, elem){
                 $(elem).find("#count_message").css("visibility", "visible");
             });
             if (securityWebSocket()){
                 console.log("EVENT1801");
              //   console.log($("#messenger"));

                 $("#chats").empty();
                 chat.send(JSON.stringify({"action": "getChats"}));
             }
         } else {
             $(".chats").children(".chat-user").each(function(index, elem){
                 $(elem).find("#count_message").css("visibility", "visible");
             });
             flagShowMessenger = true;
         }
     });*/

    var scroll = 0;

    if ("onwheel" in document) {
        // IE9+, FF17+, Ch31+
        $(".message-container")[0].addEventListener("wheel", function(){ messageContainerScroll(this);});
    } else if ("onmousewheel" in document) {
        // устаревший вариант события
        $(".message-container")[0].addEventListener("mousewheel", function(){ messageContainerScroll(this);});
    } else {
        // Firefox < 17
        $(".message-container")[0].addEventListener("MozMousePixelScroll", function(){ messageContainerScroll(this);});
    }

    $(".control-panel-button").mouseenter(function(){
        ($(this).children("img"))[0].style.transition="box-shadow 0.5s";
        ($(this).children("img"))[0].style.boxShadow="0 0 3px 3px white";
        this.style.backgroundColor="white";
    });
    $(".control-panel-button").mouseleave(function(){
        ($(this).children("img"))[0].style.transition="box-shadow 0.5s";
        ($(this).children("img"))[0].style.boxShadow="";
        this.style.backgroundColor="rgba(0, 0, 0, 0)";
    });

    ($("#editor"))[0].addEventListener("keydown", function(e) {
        if (e.keyCode === 13) {
            sendMessage();
        }
    });

    $("#editor").on("input",function(ev){
        userWrite();
    });

    securityWebSocket();
});
//}

/**
 * * * * * Функции создания отдельных представлений * * *
 */

/**
 * Создает новый блок сообщения и добавляет его в представление
 * @param {type} elem
 * @param {type} id_user
 * @returns {String}
 */
function createMessage(elem, id_user){
    console.log(elem);
    var div;
    var parent = '';
    var file = '';
    if (elem.parent_id != undefined & elem.parent_id != null & $(".message-table").find("div[id="+ elem.parent_id +"]").length != 0) {
        parent = '<div class="parent">'
            + $(".message-table").find("div[id="+ elem.parent_id +"]").find(".message").html()
            + '</div>';
    }

    if (elem.file != undefined & elem.file != null & elem.file != "") {
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
            +'" target="_blank"><img src="' + str + '" style="margin: 5px; max-width: 93%;"></img></a>';
    }

    if (id_user == Number(elem.id_user)) {
        var src;
        switch (elem.status) {
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
            + parent + '<font class="message"><b><i>' + elem.author + ': </i></b>' + elem.message + '</font>'
            + '<br><font class="time">'+ elem.time + '</font>' + file + '</div>';
    }
    return div;
}

/**
 * Создает и возвращает созданную дату
 * @param {type} date
 * @param {type} loadingDate
 * @returns {String}
 */
function createDate(date, loadingDate = false){
    /* if (loadingDate) $(".message-table").find("td").first().append('<div class="date-message-container">'
             + date + '</div>');
     else */
    return '<div class="date-message-container">' + date + '</div>';
    /*  else $(".message-container").append('<div class="date-message-container">'
              + date + '</div>');*/
}

/**
 * Возвращает новую строку таблицы
 * @param {type} div
 * @param {type} flag
 * @returns {String}
 */
function createStr(div, flag = false){
    if (flag)
        return '<tr><td align="center">' + div + '</td></tr>';

    return '<tr><td>' + div + '</td></tr>';
}

/**
 * Создает представление пользователя
 * @param {type} id_user
 * @param {type} fio
 * @param {type} username
 * @param {type} showStatusUser
 * @param {type} statusUser
 * @param {type} showStatusConnect
 * @param {type} connected
 * @returns {String}
 */
function createUser(id_user, fio, username, showStatusUser = false, statusUser = true,
                    showStatusConnect = false, connected = false){
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

/**
 * Возвращает новое представление диалога
 * @param {type} data
 * @returns {String}
 */
function createDialog(data = ""){
    return '<div id="dialog"><div id="dialog_body">' + data + '</div></div>';
}



/**
 * * * * * Функции управления графическим интерфейсом * * *
 */

/**
 * Отвечает за подсветку выбранного сообщения
 * @param {type} div
 * @returns {undefined}
 */
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

/**
 * Отображает или скрывает панель чатов
 * @return {undefined}
 */
function showLeftPanel(){
    if ($("#left-panel").css("display") != "none") {
        $("#showLeftPanel").children().attr("src", "/images/invisibility16.png");
        $("#left-panel").css("display", "none");
        $("#messengerBody").css("width", "450px");
        $("#right-panel").css("width", "100%");
        $("#editorContainer").css("width", "72%");
        $("#editorButtonsContainer").css("width", "27%");
    } else {
        $("#left-panel").css("display", "inline-block");
        $("#messengerBody").css("width", "650px");
        $("#right-panel").css("width", "57%");
        $("#editorContainer").css("width", "80%");
        $("#editorButtonsContainer").css("width", "17%");
        $("#showLeftPanel").children().attr("src", "/images/visible16.png");
    }
}





/**
 * * * * * Функции WS интерфейса * * *
 */

/**
 * Функция отвечает за проверку и инициализацию WS соединения
 * @returns {Boolean}
 */
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

/**
 * Отвечает за отображение сообщения о печатании опонента чата
 * @returns {undefined}
 */
function userWrite(){
    if (securityWebSocket()) {
        var newText = $("#editor").val();

        if (newText.length === 0) {
            lastTextSize = newText.length;
            console.log("idChat = ". idChat);
            chat.send(JSON.stringify({"action" : OP_WRITEN, "id_chat" : idChat, "write" : false}));
        } else if (lastTextSize < newText.length) {
            if ((newText.length - lastTextSize) > 10 ) {
                console.log("idChat = ". idChat);
                lastTextSize = newText.length;
                chat.send(JSON.stringify({"action" : OP_WRITEN, "id_chat" : idChat, "write" : true}));
            }
        }
    }
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

        var dataWS = {"action" : OP_NEW_MESSAGE, "message" : message, "id_chat" : idChat};
        if (right.length > 0) {
            dataWS.parent_id = Number(right[0].id);
        } else if (left.length > 0) {
            dataWS.parent_id = Number(left[0].id);
        }

        var file = $("#inputFile").prop("files")[0];
        if (file != undefined & file != null) {

            if (file.size > 31458000) {
                alert("Размер файла не должен превышать 30 Мб");   //  иначе покажем сообщение и завершим цикл
                return false;
            }
            var dataArray = new FormData();
            dataArray.append("file", file);
            dataArray.append("id_chat", idChat);

            $.ajax({
                type: "post",
                data: dataArray,                                             //  отправим наш формдата
                url: "/chat/save-file",
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
                        if (dataWS.message.length == 0) dataWS.message = dataWS.file;
                        console.log('dataWS => ');
                        console.log(dataWS);
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
 * Отвечает за подгрузку сообщений из истории
 * @param {type} target
 * @returns {undefined}
 */
function messageContainerScroll(target){
    if ($(target)[0].scrollTop == 0 & blockScroll) {
        blockScroll = false;
        getHistoryChat($(".chats").children("#" + idChat)[0], $(".message-container").find(".date-message-container").first().html(), true);
    }
}

/**
 * Обновляет список чатов (необходима для нештатной ситуации
 * @return {undefined}
 */
function updateChats(){
    if (securityWebSocket()) {
        chat.send(JSON.stringify({"action": OP_GET_CHATS }));
    }
}







/**
 * * * * * Вспомогательные функции * * *
 */

/**
 * Собирает и возвращает массив всех отмеченных пользователей в списке
 * @param {type} elem
 * @returns {Array|addUsersToMass.massiv}
 */
function addUsersToMass(elem){
    var massiv = [];
    $(elem).find("input:checkbox:checked").each(function(index, elem){
        massiv.push($(elem).attr('id'));
    });
    return massiv;
}

/**
 * Добавляет пользователей в чат
 * @returns {undefined}
 */
function addUserToChat(){
    var massUsers = addUsersToMass($("#search_result"));

    $("#dialog").remove();

    if (securityWebSocket()) {
        str = JSON.stringify({ "action" : OP_ADD_USER, "id_chat" : idChat, "users" : massUsers });
        console.log(str)
        chat.send(str);
    }
}

/**
 * Устанавливает обработчики события выбора пользователя в списке диалога
 * @returns {undefined}
 */
function setClickListenerToSearchUser(){
    $("#dialog_body").find(".search-user").on("click", function(){
        if ($(this).find("input").prop("checked")) $(this).find("input").prop("checked", false);
        else $(this).find("input").prop("checked", true);
    });
}

/**
 * Выводит в диалог всех найденных пользователей
 * @param {type} users
 * @param {type} myId
 * @returns {undefined}
 */
function showSearchUsers(users, myId){
    $.each(users, function(index, elem){        
        if (elem.id_user != myId) {
            var fio = '';
            if (elem.last_name != null && elem.last_name != '') fio = elem.last_name + ' ';
            if (elem.fist_name != null && elem.fist_name != '') fio = fio + elem.fist_name + ' ';
            if (elem.patronymic != null && elem.patronymic != '') fio = fio + elem.patronymic;
            $("#search_result").append(createUser(elem.id_user, fio, elem.user_name));
        }
     //   if (elem.id != myId) $("#dialog_body").append(createUser(elem.id, elem.fio, elem.username));
    });
    setClickListenerToSearchUser();
}

/**
 * Запускает поиск пользователей по указанным параметрам в диалогах: "новый чат"
 * и "добавить пользователей в чат"
 * @returns {undefined}
 */
function searchUser(){
    var data = {};
    $("#search_table").find("input").filter(function (index, elem){
        if ("name_chat".localeCompare(elem.id) != 0) return true;
        else return false;
    }).each(function(index, elem){
        data[elem.name] = elem.value.trim();
    });
  /*  $("#search_table").find("input").each(function(index, elem){
        data[elem.name] = elem.value.trim();
    });*/
    //console.log($(data));

    $("#search_result").empty();

    if (securityWebSocket()) {
        chat.send(JSON.stringify({"action" : OP_SEARCH_USER, "search" : data}));
    }
}

/**
 * Запускает создание нового чата
 * @returns {undefined}
 */
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
    var data = {"action" : OP_CREATE_NEW_CHAT, "chat_name" : chat_name, "users" : mass};

    if (securityWebSocket()) {
        chat.send(JSON.stringify(data));
    }
}




/**
 * * * * * Функции создания диалоговых окон * * *
 */

/**
 * Создает диалог поиска пользователя
 * @param {type} newChat
 * @returns {undefined}
 */
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
            $('.ui-dialog-titlebar-close').html('&times;');
            $('.ui-dialog-titlebar-close').css("padding", "0px");
            $('.ui-dialog-titlebar-close').attr("title", "Закрыть");
        },
        close: function (){
            $('#dialog').remove();
        },
        title: dialogTitle,
        autoOpen: true,
        width: 500,
        height: 500
    });
}

/**
 * Создает диалог списка пользователей чата
 * @returns {undefined}
 */
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
                //    console.log(massUsers);
                    if  (securityWebSocket()) {
                        chat.send(JSON.stringify({"action": OP_REMOVE_USER, "id_chat": idChat, "users": massUsers}));
                    }
                }
            },
            { text: "Заблокировать",
                click: function (){
                    var massUsers = addUsersToMass($("#dialog_body"));
                    if (massUsers.length > 0) {
                        if  (securityWebSocket()) {
                            str = JSON.stringify({ "action": OP_BLOCK_USERS, "users": massUsers, "blackList": false })
                            console.log(str)
                            chat.send(str);
                        }
                    }
                }
            },
            { text: "Разблокировать",
                click: function (){
                    var massUsers = addUsersToMass($("#dialog_body"));
                    if (massUsers.length > 0) {
                        if  (securityWebSocket()) {
                            str = JSON.stringify({ "action": OP_UNLOOCK_USERS, "users": massUsers, "blackList": false })
                            console.log(str)
                            chat.send(str);
                        }
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
        str = JSON.stringify({ "action" : OP_LIST_USERS, "id" : idChat })
        console.log(str);
        chat.send(str);
    } else {
        alert("WS соединение закрыто, перезагрузите страницу");
    }
}

/**
 * Создает диалог черного списка пользователей
 * @returns {undefined}
 */
function createBlackListDialog(){
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
                        if (securityWebSocket()) {
                            chat.send(JSON.stringify({ "action" : OP_UNLOOCK_USERS, "users" : massUsers, "blackList" : true }));
                        }
                    }
                }
            }
        ],
        title: "Черный список",
        autoOpen: true,
        width: 500,
        height: 520
    });
    if (securityWebSocket()) {
        chat.send(JSON.stringify({ "action" : OP_BLACK_LIST_USERS }));
    }
}

/**
 * Функция покидания чата
 * @returns {undefined}
 */
function createExitChatDialog(){
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
                var mass = addUsersToMass($("#dialog_body"));
                console.log("MassivUSERS")
                console.log(mass)
                var data = { "action" : OP_EXIT_CHAT, "id" : idChat };
                if (mass.length != 0) data.id_user = mass[0];
                console.log(data);

                if  (securityWebSocket()) {
                    chat.send(JSON.stringify(data));
                    $(this).dialog("close");
                }
            }
        }],
        title: "Выход из чата",
        autoOpen: true,
        width: 500,
        height: "520"
    });
    if  (securityWebSocket()) {
        chat.send(JSON.stringify({ "action" : OP_LIST_USERS, "id" : idChat }));
    }
}

/**
 * Функция удаления чата
 * @returns {undefined}
 */
function createDeleteChatDialog(){

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
                if  (securityWebSocket()) {
                    chat.send(JSON.stringify({"action" : OP_REMOVE_CHAT, "id" : idChat }));
                    $(this).dialog("close");
                }
            }
        }],
        title: "Удаление чата",
        autoOpen: true,
        width: 500,
        height: "auto"
    });
}




/**
 * * * * * Основные системные функции * * *
 */

/*
 * Создает и заполняет начальный чат сообщениями
 * @param {type} data
 * @returns {undefined}
 */
function initializationChat(data){                                                      //  при первом запуске наполняет окно сообщениями из истории
    $(".message-container").append('<table class="message-table"></table>');
    first = null;
    d = null
    for (key in data.messages){
        d = data.messages[key].date;
        break;
    }
    if (!d) {
        var date = new Date();
        d = date.getFullYear() + '-' + date.getMonth() + '-' + date.getDate();
    }

    $(".message-table").append(createStr(createDate(d), true));
    for (key in data.messages) {
        $(".message-table").append(createStr(createMessage(data.messages[key], idUser)));           //  конструктор сообщений
    }
    $(".message-container")[0].scrollTop = $(".message-container")[0].scrollHeight;         //  прокручиваем историю чата до последнего сообщения
}

/*
 * Выводит подгруженные данные чата (предыдущая дата). Сообщения придут отсортированные
 * в обратной временной последовательности, т.е. ["0" => 23:59,
 *                                                "1" => 22:59,
 *                                                "n" => 00:00]
 * @returns {undefined}
 */
function loadingChat(data){                                                     //  создаем заглавие чата в виде даты
    console.log('data');
    console.log(data);
    var table = $(".message-table")[0];
    var i = "";
    $.each(data.messages, function(index, elem){
        i= index;
        $(".message-table").prepend(createStr(createMessage(elem, idUser)));           //  конструктор сообщений
    });
   // console.log(i)
    $(".message-table").prepend(createStr(createDate(data.messages[i].date), true));
}

/*
 * Загружает данные чата последней доступной даты или подгружает данные указанной даты
 * @param {type} event target
 * @returns {undefined}
 */
function getHistoryChat(target, date = null, loadingData = false){
    console.log(arguments);

    $("#editor").val("");
    /*if (idChat != null)*/
    $(".chat-user").filter(function (index, elem){
        return true;
    }).css("background-color", "#d66620");
    idChat = target.id;
    target.style.backgroundColor = "#993300";

    var dataSend = { "id" : target.id };
    if (date !== null) dataSend.date = date;

    if (loadingData == false) $(".message-container").empty();
    dataSend.action = OP_GET_HISTORY_MESSAGE;

    dataSend.loadingData = loadingData;

    console.log(dataSend);
    if  (securityWebSocket()) {
        chat.send(JSON.stringify(dataSend));
    } else {
        console.log("Соединение оборвано");
    }
    /* $.ajax({
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
     });*/
}

/*
 * Создает новый WS канал связи и обрабатывает события этого канала
 * @returns {undefined}
 */
function createWebSocket(){
    chat = new WebSocket(protocol + '://' + HOST_NAME /*window.location*/ + port + adress);

    chat.onopen = function(e) {                                                 //  событие возникает при успешном открытии канала
        console.log("Соединение WS -> ok");
        if (userName == "") return;
        console.log("Отправляемые данные: ");
        var d = {"action" : OP_SET_USER_NAME, "name" : userName};
        //var d = {"action" : "setName", "name" : userName};
        console.log(d);
        chat.send(JSON.stringify(d));

    };

    chat.onerror = function(e) {
        alert('Ошибка соединения с сервером, перезагрузите страницу. Если сообщение повторяется более 3 раз сообщите программисту');
    };

    chat.onmessage = function(e) {

        var response = JSON.parse(e.data);
        console.log(response);
        if (response.status.status.operation == "name") {
            return;
        }
        if (response.pong !== undefined) {
            // console.log("status = false, s_message = " + response.s_message);
            //       console.log(response);
            //       return;
        }
        switch (Number(response.status.status.operation)){
            case OP_ERROR_NAME:
                break;
            case OP_SET_USER_NAME:
                if (Number(response.status.status.status) != STATUS_ACCEPT){
                    alert("Чат не активирован, перезагрузите страницу")
                    return;
                }
                console.log("WS name -> ok");

                for (key in response.users){
                    idUser = Number(response.users[key].id);
                    userName = response.users[key].username;
                    break;
                }

                if ($("#chats").children().length == 0){
                    chat.send(JSON.stringify({"action": OP_GET_CHATS}));
                }

                console.log(massMessages);
                massMessages.forEach(function (item, i, arr){
                    chat.send(JSON.stringify(item));
                });
                massMessages = [];
                break;
            case OP_STATUS_MESSAGE: {
                //  $(response).each(function(i, one){
                if (Number(response.status.status.status) != STATUS_ACCEPT) return;

                for (i in response.messages) {
                    if (idChat == Number(response.messages[i].id_chat)) {
                        var imgName = "";
                        $(".message-container :first").find(".message-left").each(function (index, elem) {
                            if (elem.id == response.messages[i].id) {
                                switch (response.messages[i].status) {
                                    case MESSAGE_SEND:
                                        imgName = "/images/send.png";
                                        break;
                                    case MESSAGE_DELIVERED:
                                        imgName = "/images/delivered.png";
                                        break;
                                    case MESSAGE_READED:
                                        imgName = "/images/readed.png";
                                        break;
                                    default:
                                        alert("картинка не найдена");
                                }
                                $(elem).find("img:first").attr("src", imgName);
                            }
                        });
                    }
                }

             /*   if (idChat !== response.me.id_chat) {                        //  проверяем, что статус поменялся у сообщения из открытого чата иначе сброс
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
                        }
                    });
                }*/
                //    });
            }
                break;
            case OP_INPUT_MESSAGE: {
                    console.log(response.messages);
                var key = null;
                for (k in response.messages) { key = k; break; }

                if ((Number(response.status.status.status) != STATUS_ACCEPT) || key == null) {
                    console.log("ПОВРЕЖДЕННОЕ СООБЩЕНИЕ");
                    return;
                }
                // response.status_message = response.s_code;  на случай если с сервера придет сообщение со статусом отличным от SEND
                    //  очищаем таблицу сообщений от оповещений активности участников
                $(".message-table").find('.write[id='+ response.messages[key].author +']').closest("tr").remove();

                    //  если чат светнут показываем колокольчик на кнопке свертывания чата
                if ($("#messengerBody").attr("aria-expanded") == "false" 
                        || $("#messengerBody").attr("aria-expanded") == null) {
                    $("#head_alarm").css("display", "block");
                }
                var status = MESSAGE_DELIVERED;

                    //  проверяем что сообщение из активного(открытого) чата
                if (Number(response.messages[key].id_chat) != idChat) {
                    $(".chats").children(".chat-user").each(function (index, elem) {
                        if (elem.id == response.messages[key].id_chat) {
                            $(elem).find("#count_message").css("visibility", "visible");
                            var count = Number($(elem).find("#text_count").html());
                            if (count !== 0 & count !== NaN) count += 1;
                            else count = 1;
                            $(elem).find("#text_count").html(count);
                        }
                    });
                    //   if ($("#messengerBody").attr("aria-expanded") == "true")
                } else if ("true".localeCompare($("#messengerBody").attr("aria-expanded")) == 0) {                    //  проверяем, что новое сообщение из открытого чата, иначе стоп
                    status = MESSAGE_READED;
                    df = createMessage(response.messages[key], idUser);
                    $(".message-table").append(createStr(df));
                    $(".message-container")[0].scrollTop = $(".message-container")[0].scrollHeight;
                }

                $(".chat-user").each(function(index, elem){
                    if (elem.id == response.messages[key].id_chat) {
                        $(elem).find("#last_message").html(response.messages[key].author + ": <i>" + response.messages[key].message + "</i>");
                        $(elem).find("#last_date").html(response.messages[key].time);
                    }
                });

                if (Number(response.messages[key].id_user) != idUser) {
                    if (securityWebSocket()) {
                        chat.send(JSON.stringify({
                            "action" : OP_STATUS_MESSAGE, "id_chat" : response.messages[key].id_chat,
                            "id" : response.messages[key].id, "status" : status
                        }));
                    } else {
                        alert("WS соединение закрыто, перезагрузите страницу");
                    }
                }
            }
                break;
            case OP_SEARCH_USER: {
                if (Number(response.status.status.status) == STATUS_ACCEPT) {
                    showSearchUsers(response.users, idUser);
                } else alert("Не найдено совпадений");
            }
                break;
            case OP_GET_CHATS: {
                if (Number(response.status.status.status) == STATUS_ACCEPT) {
                    if ($("#chats").children().length > 0) $("#chats").empty();
                    for (var key in response.chats){
                        var elem = response.chats[key];
                        var status = '';
                        var autor = '';
                        var chatUser = '';
                        var chatDate = '';
                        var chatMessage = '';
                        var alarm = false;

                        if (elem['status'] == CHAT_DIACTIVATED) status = 'style="background-color: grey; border-color: black;"';
                        if (idUser == Number(elem['author'])) autor = '&copy;';

                        if (response.messages[elem['id']] != null /* && response.users[response.messages[elem['id']]['id_user']] != null*/) {
                            chatUser = response.messages[elem['id']]['username'] +': <i>'+ response.messages[elem['id']]['message'] +'</i>';
                            chatDate = /*strftime('%e %b', strtotime(*/response.messages[elem['id']]['date'];
                            if (chatDate == null /*strftime('%e %b')*/) chatDate = response.messages[elem['id']]['time'];
                            //   echo strftime('%e %b', strtotime($messages[$one['id']]['date']));

                            if (response.messages[elem['id']]['status'].localeCompare("readed") != 0 
                                    && Number(response.messages[elem['id']]['id_user']) != Number(idUser)) { 
                                alarm = true; 
                                console.log("id_chat => " + elem['id'] + ", idUser => " + idUser 
                                        + ",  alarm => true");
                            }
                        }

                        $("#chats").append('<table id="'+ elem['id'] +'" rows="2" cols="4" class="chat-user" onclick="getHistoryChat(this)" >'
                            +'<tr>'
                            +'<td class="user-td" id="chat_name" colspan="2" style="font-size: 11pt; width: auto;"><b>'+ elem['alias']  +'</b></td>'
                            +'<td class="user-td" id="chat_autor" style="width: 30px; padding-left: 5px;">'+ autor +'</td>'
                            +'<td class="user-td" id="chat_alarm" style="width: 40px;">'
                            +'<div id="count_message" class="alarm">'
                            +'<font id="text_count" style="text-align: center; vertical-align: middle;"></font>'
                            +'</div>'
                            +'</td>'
                            +'</tr>'
                            +'<tr>'
                            +'<td colspan="2" class="user-td" id="last_message" style="padding-bottom: 5px; padding-top: 0px; font-size: 9pt;">'+ chatUser +'</td>'
                            +'<td class="user-td" id="last_date" colspan="2" style="text-overflow: clip;'
                            +'padding-left: 4px; padding-top: 0px; padding-bottom: 5px; padding-right: 5px; font-size: 8pt; font-style: italic;">'+ chatDate +'</td>'
                            +'</tr>'
                            +'</table>');
                        if (alarm) {
                            $(".chat-user[id="+ elem['id'] +"]").find(".alarm").css("visibility", "visible")
                        }
                    }

                    $(".chat-user").mouseenter(function(){
                        this.style.width="240px";
                        this.style.height="60px";
                        this.style.boxShadow="7px 10px 1px 1px #999999, 0 0 20px 10px #cccccc";
                    });
                    $(".chat-user").mouseleave(function(){
                        this.style.width="220px";
                        this.style.height="40px";
                        this.style.boxShadow="3px 4px 1px 0px #999999, 0 0 20px 10px #cccccc";
                    });
                    if (idChat != null) getHistoryChat($(".chat-user[id="+ idChat +"]")[0]);        //  Загружает историю сообщений если уже был выбран какой-либо из чатов
                    else if ($(".chat-user").length > 0) getHistoryChat(($(".chat-user"))[0]);      //  Если не выбран не один, то грузим первый попавшийся

                } else alert("Не найдено совпадений");
            }
                break;
            case OP_SET_USER_NAME: {
                if (Number(response.status.status.status) == STATUS_ACCEPT) {
                    for(key in response.users) {
                        idUser = response.users[key].id;
                        console.log(idUser);
                        setName = false;
                    }
                } else {
                    console.log("IDUSER not found => " + idUser);
                }
            }
                break;
            case OP_CREATE_NEW_CHAT: {
                if (Number(response.status.status.status) == STATUS_ACCEPT) {
                    var autor = '';
                    for (key in response.chats) {
                        if (Number(response.chats[key].author) == idUser) {
                            autor = '&copy;';
                        }
                        $("#chats").prepend(
                            '<table id="' + response.chats[key].id + '" rows="2" cols="4" class="chat-user" onclick="getHistoryChat(this)" >\n\
                                <tr><td class="user-td" id="chat_name" colspan="2" style="font-size: 11pt; width: auto;"><b>' + response.chats[key].alias
                            + '</b></td><td class="user-td" id="chat_autor" style="width: 20px; padding-left: 5px;">' + autor
                            + '</td><td class="user-td" id="chat_alarm" style="width: 40px;"><div id="count_message" class="alarm">\n\
                            <font id="text_count" style="text-align: center; vertical-align: middle;"></font></div></td></tr><tr>\n\
                            <td colspan="2" class="user-td" id="last_message" style="padding-bottom: 10px; padding-top: 0px; font-size: 9pt;">'
                            + '</td><td class="user-td" id="last_date" colspan="2" style="text-overflow: clip; padding-left: 4px;\n\
                             padding-top: 0px; padding-bottom: 10px; padding-right: 5px; font-size: 9pt; font-style: italic;">'
                            + '</td></tr></table>');
                        idChat = Number(response.chats[key].id);
                    }
                    getHistoryChat($(".chat-user[id="+ idChat +"]")[0]);
                }
            }
                break;
            case OP_WRITEN: {
                if (Number(response.status.status.status) != 1) return;
                for (key in response.users) {
                    if (idUser != Number(key) & idChat == Number(response.users[key].id_chat)) {
                        if (response.users[key].write.localeCompare("true") == 0) {
                            if ($(".message-table").find(".write[id="+ response.users[key].username +"]").length > 0)
                                $(".message-table").find(".write[id="+ response.users[key].username +"]").remove();
                            $(".message-table").append(createStr('<div id="'+ response.users[key].username +'" class="write">'+ response.users[key].username +' печатает</div>'));
                            $(".message-container")[0].scrollTop = $(".message-container")[0].scrollHeight;
                        } else {
                            $(".message-table").find(".write[id="+ response.users[key].username +"]").remove();
                        }
                    }
                }
             //   if (response.id_chat == idChat) {
                    if (response.s_code) {
                        if ($(".message-table").find(".write[id="+ response.author +"]").length > 0)
                            $(".message-table").find(".write[id="+ response.author +"]").remove();
                        $(".message-table").append(createStr('<div id="'+ response.author +'" class="write">'+ response.author +' печатает</div>'));
                        $(".message-container")[0].scrollTop = $(".message-container")[0].scrollHeight;
                    } else {
                        $(".message-table").find(".write[id="+ response.author +"]").remove();
                    }
              //  }
            }
                break;
            case OP_GET_HISTORY_MESSAGE: {
                blockScroll = true;
                if (Number(response.status.status.status) == STATUS_ACCEPT) {
                    if (response.status.status.loadingData.localeCompare("true") == 0) {
                        for (key in response.messages) {
                                loadingChat(response);
                                return;
                        }
                        console.log("Больше нет сообщений");
                    } else {
                        $(".chat-user").each(function (index, elem){
                            if (elem.id == idChat) {
                                $(elem).find("#count_message").css("visibility", "hidden");
                                $(elem).find("#text_count").html("0");
                            }
                        });
                        initializationChat(response);
                        if (securityWebSocket()) {
                            chat.send(JSON.stringify({
                                "action" : OP_STATUS_MESSAGE,
                                "id" : "0",
                                "id_chat" : idChat,
                                "status" : MESSAGE_READED
                            }));
                        } else {
                            alert("WS соединение закрыто, перезагрузите страницу");
                        }
                    }
                } else { console.log(response.s_message); }
            }
                break;
            case OP_EXIT_CHAT: {
                if (Number(response.status.status.status) == STATUS_ACCEPT) {
                    for (key in response.chats) {
                        if (Number(response.chats[key].author) == idUser) {
                            var autor = '&copy;';
                            $(".chat-user").filter(function (index, elem) {
                                console.log(elem);
                                return Number(elem.id) == Number(response.chats[key].id);
                            }).find("#chat_autor")[0].innerHTML = autor;
                        }
                        if (Number(response.chats[key].id_user) == idUser) {
                            idChat = null;
                            chat.send(JSON.stringify({"action": OP_GET_CHATS}));
                            alert("Вы покинули чат");
                            return;
                        }
                        if (Number(response.chats[key].id) == idChat) {
                            alert(response.users[response.chats[key].id_user].username + " покинул чат");
                        }
                        break;
                    }
            /*        console.log("Вы покинули чат");
                    idChat = null;
                    chat.send(JSON.stringify({"action": "getChats"}));*/
                }
            }
                break;
            case OP_LIST_USERS: {
                if (Number(response.status.status.status) == STATUS_ACCEPT) {
                    var status;
                    
                    console.log(response);
                    
                    $.each(response.users, function (index, elem){
                        status = true;
                        if (idUser != Number(elem.id_user)) {
                            $.each(response.black_list, function (i, k){
                                if (Number(i) == Number(elem.user_id)) status = false;
                            });
                            connected = false;
                            if ("true".localeCompare(elem.connected) == 0) connected = true;
                            var fio = '';
                            if (elem.last_name != '') fio = fio + elem.last_name;
                            if (elem.fist_name != '') fio = fio + ' ' + elem.fist_name;
                            if (elem.patronymic != '') fio = fio + ' ' + elem.patronymic;
                            $("#dialog_body").append(createUser(elem.id_user, fio, elem.user_name, true, status, true, connected));
                        }
                    });
                    setClickListenerToSearchUser();
                } //else $("#dialog_body").append(response.s_message);
            }
                break;
            case OP_REMOVE_USER: {
                if (Number(response.status.status.status) == STATUS_ACCEPT) {
                    $("#dialog_body").empty();
                    chat.send(JSON.stringify({ "action" : OP_LIST_USERS, "id" : idChat }));
                } else alert(response.status.status.message);
            }
                break;
            case OP_ADD_USER: {
                if (Number(response.status.status.status) == STATUS_ACCEPT) {
                    createUsersOfChatDialog();
                } else alert(response.status.status.message);
            }
                break;
            case OP_BLOCK_USERS: {
                if (Number(response.status.status.status) == STATUS_ACCEPT) {
                    $.each(response.users, function (i, k){
                        $("#dialog_body").find(".search-user").each(function (index, elem){
                            if (Number(i) == Number(elem.id)) {
                                $(elem).css("background-color", "grey");
                                $(elem).find("#status").html(" заблокирован");
                            }
                        });
                    });
                }
            }
                break;
            case OP_UNLOOCK_USERS: {
                if (Number(response.status.status.status) == STATUS_ACCEPT) {
                    $.each(response.users, function (i, k){
                        if ("false".localeCompare(response.status.status.blackList) == 0){
                            $("#dialog_body").find(".search-user").each(function (index, elem){
                                if (Number(i) == Number(elem.id)) {
                                    $(elem).css("background-color", "white");
                                    $(elem).find("#status").html(" активен");
                                }
                            });
                        } else {
                            $.each(response.users, function (index, elem){
                                $("#dialog_body").find(".search-user[id="+ index +"]").remove();
                            });
                        }
                    });
                }
            }
                break;
            case OP_BLACK_LIST_USERS: {
                if (Number(response.status.status.status) == STATUS_ACCEPT) {
                    $.each(response.users, function (index, elem){
                        var fio = '';
                        if (elem.last_name != '') fio = fio + elem.last_name;
                        if (elem.fist_name != '') fio = fio + ' ' + elem.fist_name;
                        if (elem.patronymic != '') fio = fio + ' ' + elem.patronymic;
                        $("#dialog_body").append(createUser(elem.id_user, fio, elem.user_name, true, false));
                    });
                    setClickListenerToSearchUser();
                } else console.log(response.s_message);
            }
                break;
            case OP_REMOVE_CHAT: {
                if (Number(response.status.status.status) == STATUS_ACCEPT) {
                    var message = null;
                    for (key in response.chats) {
                        message = $(".chat-user[id=" + response.chats[key].id + "]").find("td[id=chat_name]")[0].innerHTML
                        message = "Чат \"" + message + "\" был удален пользователем " + response.users[response.chats[key].author].username
                        $(".chat-user[id=" + response.chats[key].id + "]").remove();
                        if (Number(idChat) == Number(response.chats[key].id)) $(".message-container").empty();
                        $("#dialog").dialog("close");
                    }
                    if (message != null) alert(message);
                } else alert(response.status.status.message);
            }
                break;
            default: console.log("НЕИЗВЕСТНЫЙ ТИП ОПЕРАЦИИ");
          //      console.log(response);
                break;
        }

        /*    if (response.type && response.type == 'chat') {
                $('#sp1-content').append(createBlock('<b>' + response.from
                        + '</b>: ' + response.message ));
                $('#sp1-content').scrollTop = $('#sp1-content').height;
                if ($('#sp1-content').children().length > 0) $('#openChat')[0].style.backgroundColor = "red";
            } //*/
    };
}


