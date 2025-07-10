// js/map.js - Loads Google Map, route, and nearby attractions
function initMap() {
    // These variables should be set by PHP or JS in the page
    var from = typeof window.fromLocation !== 'undefined' ? window.fromLocation : null;
    var to = typeof window.toLocation !== 'undefined' ? window.toLocation : null;
    var cityName = typeof window.cityName !== 'undefined' ? window.cityName : null;
    var placeList = typeof window.placeList !== 'undefined' ? window.placeList : [];

    // Default center
    var center = { lat: 20.5937, lng: 78.9629 }; // India
    var map = new google.maps.Map(document.getElementById('map'), {
        center: center,
        zoom: 5
    });

    // If from/to are provided, show route
    if (from && to) {
        var directionsService = new google.maps.DirectionsService();
        var directionsRenderer = new google.maps.DirectionsRenderer();
        directionsRenderer.setMap(map);
        directionsService.route({
            origin: from,
            destination: to,
            travelMode: 'DRIVING' // or 'TRANSIT', 'WALKING', 'BICYCLING'
        }, function(result, status) {
            if (status === 'OK') {
                directionsRenderer.setDirections(result);
                // Show travel time and distance
                var leg = result.routes[0].legs[0];
                var info = document.createElement('div');
                info.innerHTML = `<b>Estimated Time:</b> ${leg.duration.text} <br><b>Distance:</b> ${leg.distance.text} <br><b>Estimated Cost:</b> ₹${(leg.distance.value/1000*5).toFixed(0)}`;
                info.style.background = '#fff';
                info.style.padding = '10px';
                info.style.margin = '10px 0';
                info.style.borderRadius = '8px';
                document.getElementById('map').parentNode.insertBefore(info, document.getElementById('map'));
            }
        });
    }

    // If cityName and placeList are provided, show markers
    if (cityName && placeList.length > 0) {
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({ address: cityName }, function(results, status) {
            if (status === 'OK') {
                var cityLocation = results[0].geometry.location;
                map.setCenter(cityLocation);
                map.setZoom(13);
                var service = new google.maps.places.PlacesService(map);
                placeList.forEach(function(placeName) {
                    service.findPlaceFromQuery({
                        query: placeName + ' in ' + cityName,
                        fields: ['name', 'geometry']
                    }, function(results, status) {
                        if (status === google.maps.places.PlacesServiceStatus.OK && results[0]) {
                            new google.maps.Marker({
                                map: map,
                                position: results[0].geometry.location,
                                title: placeName
                            });
                        }
                    });
                });
            }
        });
    }
}
