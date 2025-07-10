// Initialize Google Maps
let map;
let marker;

function initMap(city) {
    const cityCoords = {
        'paris': { lat: 48.8566, lng: 2.3522 },
        'london': { lat: 51.5074, lng: -0.1278 },
        'new_york': { lat: 40.7128, lng: -74.0060 },
        'tokyo': { lat: 35.6762, lng: 139.6503 },
        'dubai': { lat: 25.2048, lng: 55.2708 },
        'singapore': { lat: 1.3521, lng: 103.8198 },
        'mumbai': { lat: 19.0760, lng: 72.8777 },
        'delhi': { lat: 28.6139, lng: 77.2090 },
        'jaipur': { lat: 26.9124, lng: 75.7873 },
        'goa': { lat: 15.4989, lng: 73.8278 },
        'udaipur': { lat: 24.5854, lng: 73.7125 },
        'kerala': { lat: 10.8505, lng: 76.2711 }
    };

    const mapDiv = document.getElementById('map');
    if (!mapDiv) return;

    const coordinates = cityCoords[city] || { lat: 20.5937, lng: 78.9629 }; // Default to India

    // Create map
    map = new google.maps.Map(mapDiv, {
        center: coordinates,
        zoom: 12,
        styles: [
            {
                "featureType": "all",
                "elementType": "geometry",
                "stylers": [{"color": "#f5f5f5"}]
            },
            {
                "featureType": "water",
                "elementType": "geometry",
                "stylers": [{"color": "#c9c9c9"}]
            },
            {
                "featureType": "water",
                "elementType": "labels.text.fill",
                "stylers": [{"color": "#9e9e9e"}]
            }
        ]
    });

    // Add marker
    if (marker) marker.setMap(null);
    marker = new google.maps.Marker({
        position: coordinates,
        map: map,
        title: city.charAt(0).toUpperCase() + city.slice(1),
        animation: google.maps.Animation.DROP
    });

    // Add info window
    const infoWindow = new google.maps.InfoWindow({
        content: `<div style="padding: 10px;">
            <h3 style="margin: 0 0 5px 0;">${city.charAt(0).toUpperCase() + city.slice(1)}</h3>
            <p style="margin: 0;">Your destination</p>
        </div>`
    });

    marker.addListener('click', () => {
        infoWindow.open(map, marker);
    });
}

// Error handler for Google Maps
function handleMapError() {
    const mapDiv = document.getElementById('map');
    if (mapDiv) {
        mapDiv.innerHTML = `
            <div style="padding: 20px; text-align: center; background: #f8f9fa; border-radius: 8px;">
                <h3 style="color: #dc3545; margin-bottom: 10px;">Map Loading Error</h3>
                <p>We couldn't load the map at this time. Please try again later.</p>
            </div>
        `;
    }
}

// Export functions
window.initMap = initMap;
window.handleMapError = handleMapError; 