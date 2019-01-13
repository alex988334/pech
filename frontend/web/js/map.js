/*


ymaps = document.getElementsByTagName('ymaps');
$(ymaps).ready(function (){
    
    var shirota = document.getElementById('zakazi-shirota');
    var dolgota = document.getElementById('zakazi-dolgota');

   /* function c(e,t){
        c.superclass.constructor.call(this,e,t),
        this.events.addController(o),
        this.events.fire("create",new a({type:"create",target:this}))
    }
    */
   /* 
    point = ymaps.GeoObject({
        geometry : {
            type: 'Point',
            coordinates : [shirota.value, dolgota.value]            
        },
        properties : {
           // balloonContentBody : map_point[i]['body']
            hintContent : 'Заявка 17'
        }
    },
    {
        draggable: true,
        //iconImageHref: '/i/' + map_point[i]['spec']+'.png',
        iconImageSize: [29,29],
        //balloonIconImageHref: '/i/' + map_point[i]['spec']+'.png',
        balloonIconImageSize: [29,29],
        hasBalloon: true
    });
    var myGeoObject = new ymaps.GeoObject({
        geometry: {
            type: "Point", // тип геометрии - точка
            coordinates: [55.8, 37.8] // координаты точки
        }
    });

// Размещение геообъекта на карте.
//myMap.geoObjects.add(myGeoObject); 
    console.log(myGeoObject);

 /*  var clusterer = new ymaps.Clusterer();
   clusterer.add(point);
   map.geoObjects.add(clusterer);
   console.log(ymaps);
   
});*/
