
var data = {
    "action" : OP_SYSTEM, 
    "id" : id, 
    "status" : 150 
};
if (securityWebSocket()) { 
    console.log("ОТПРАВКА"); 
    console.log(data); 
    chat.send(JSON.stringify(data)); 
} else { 
    massMessages.push(data); 
    console.log("Ожидание отправки"); 
    console.log(massMessages);
}

