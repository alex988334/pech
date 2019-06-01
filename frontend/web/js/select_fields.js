function saveSelectedFields(){   
    var massChange = []; 
    $("#selectedFields").find("input").each(function (index, elem){
        massChange.push({id: elem.id, visible: elem.checked});        
    });
    console.log(massChange);
    $.ajax({
        url: "/manager/save-fields", 
        type: "POST",  
        dataType: "json",
        data: {"mass" : massChange},                                             
        success: function(data){  
           if (data.status == 1) location.reload();
           else console.log(data);
        },        
        error: function(error){
            console.log('Error!', error);
        }
    });    
}