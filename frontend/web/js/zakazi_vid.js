
$('#vid_region').change(function(){
    $.ajax({    
        url: "/zakaz/vid", 
        type: "POST",   
        data: { 'selectedRegion' : $(this).val()},                                             
        success: function(data){            
            data = JSON.parse(data);             
            $('#container_buttons').find('a').each(function (key, one){             
                let zakaz = $(data['zakaz']).filter(function (k, elem){
                    return elem['id_vid_work'] == one.id;
                });  
                
                let name = $(data['vid']).filter(function (k, elem){                        
                        return elem['id'] == one.id;
                    })[0]['name'];
                    
                if (zakaz.length > 0) {
                    one.innerHTML = 'Вид : ' + name 
                            + ', Всего заявок : ' + zakaz[0]['vsego']                        
                            + ', Общая сумма работ : ' + zakaz[0]['cena'];
                } else {
                    one.innerHTML = 'Вид : ' + name
                    + ', Всего заявок : 0, Общая сумма работ : 0';
                }
            });                              
        },
        statusCode: {
            404: function() {
                console.log( "page not found" );
            }
        },
        error: function(error){
            console.log('Error!', error);
        }
    });
});


