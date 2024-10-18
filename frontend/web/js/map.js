var map;
var searchControl;
ymaps.ready(function(){  
    
    var point = null;
    if (shirota != null && dolgota != null) point = [shirota, dolgota];
    else point = [shirota_change, dolgota_change];
    map = new ymaps.Map("map", { center: point, zoom: 14 });
    
//console.log([shirota, dolgota, shirota_change, dolgota_change]);
    if (shirota_change != null && dolgota_change != null) {
        myPlacemark = new ymaps.Placemark([shirota_change, dolgota_change],
                {}, {'iconColor': '#5fa3ff'});
        map.geoObjects.add(myPlacemark);
    }
    if (shirota != null && dolgota != null) {
        myPlacemark = new ymaps.Placemark([shirota, dolgota], {}, {'iconColor': '#ff8e85'});
        map.geoObjects.add(myPlacemark);
    }    
    
    
    if (moove) {        
        searchControl = map.controls.get('searchControl');
        searchControl.events.add('load', function(e){
            map.geoObjects.removeAll();
        });
        searchControl.events.add('resultselect', function (e) {
            map.events.add('balloonopen', function(){
                var coord = map.balloon.getData().geometry._coordinates;
                $("#zakaz-shirota").val(coord[0]);
                $("#zakaz-dolgota").val(coord[1]);
            });                    
        });
    
        map.events.add('click', function (e) {               
            var coords = e.get('coords');
            myPlacemark = new ymaps.Placemark([coords[0], coords[1]], {}, {'iconColor': '#ff8e85'});            
            map.geoObjects.removeAll();
            map.geoObjects.add(myPlacemark);
            $("#zakaz-shirota").val(coords[0]);
            $("#zakaz-dolgota").val(coords[1]);                   
        });
    }
}); 
 