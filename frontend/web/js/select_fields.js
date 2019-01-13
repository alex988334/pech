function saveSelectedFields(){   
    console.log(massChange);
    $.ajax({
        url: "/manager/save-fields", 
        type: "POST",  
        dataType: "json",
        data: {"mass" : massChange},                                             
        success: function(data){  
            console.log(data);
            if (data.status == 1) console.log("СОХРАНЕНИЕ УСПЕШНО");
            massChange = [];
            location.reload();
        },        
        error: function(error){
            console.log('Error!', error);
        }
    });    
}





