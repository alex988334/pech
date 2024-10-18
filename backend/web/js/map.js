var map;                                                                        //  сслка на оъект карты 
var searchControl;                                                              //  ссылка на панель поиска
ymaps.ready(function(){                                                         //  после завершения загрузки
    
    var point = [shirota, dolgota];                                             //  координаты текущего региона, из php
    var zoom = 14;
    if (shirota == 61.698653 && dolgota == 99.505405) {                             
        zoom = 2;
    }
    map = new ymaps.Map("map", { center: point, zoom: zoom });                  //  создаем карту

    myPlacemark = new ymaps.Placemark(point, {}, {'iconColor': '#ff8e85'});     //  устанавливаем метку на карте
    map.geoObjects.add(myPlacemark);
    
    if (moove) {                                                                //  если разрешено перемещение карты
            //  если в контроллере поиска появились свежие результаты, то очищаем крту
        searchControl = map.controls.get('searchControl');
        searchControl.events.add('load', function(e){
            map.geoObjects.removeAll();
        });
            //  добавляем на карту все найденные объекты
        searchControl.events.add('resultselect', function (e) {
            map.events.add('balloonopen', function(){
                var coord = map.balloon.getData().geometry._coordinates;
                $("#vidregion-shirota").val(coord[0]);
                $("#vidregion-dolgota").val(coord[1]);
            });                    
        });
            //  при событии клика по карте выставляем метку
        map.events.add('click', function (e) {               
            var coords = e.get('coords');
            myPlacemark = new ymaps.Placemark([coords[0], coords[1]], {}, {'iconColor': '#ff8e85'});            
            map.geoObjects.removeAll();                                         //  удалим все метки с карты
            map.geoObjects.add(myPlacemark);                                    //  добавим новую
            $("#vidregion-shirota").val(coords[0]);                             //  запомним координаты новой метки
            $("#vidregion-dolgota").val(coords[1]);                   
        });
    }
}); 
 