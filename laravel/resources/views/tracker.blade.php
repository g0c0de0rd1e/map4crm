<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OSM Location Tracker</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
</head>
<body>
    <div id="map" style="width: 100%; height: 500px;"></div>
    <button onclick="confirmOrder()">Подтвердить заказ</button>
    <script>
        var map = L.map('map').setView([44.952117, 34.102417], 10); // Центр карты в Крыму
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var deliveryMarker;
        var userLocation = null;
        var routingControl = null;

        fetch('/get-user-location')
        .then(response => response.json())
        .then(data => {
            console.log('User location:', data); 
            if (data.lat && data.lon) {
                userLocation = { lat: data.lat, lon: data.lon };
                placeMarker(userLocation);
            }
        })
        .catch(error => console.error('Error fetching user location:', error));


        function confirmOrder() {
            if (userLocation) {
                placeMarker(userLocation);
            } else {
                map.locate({setView: true, maxZoom: 16});
            }
        }

        function placeMarker(latlng) {
            if (deliveryMarker) {
                map.removeLayer(deliveryMarker);
            }
            deliveryMarker = L.marker(latlng).addTo(map);
            console.log('Marker placed at:', latlng);
            fetch('/confirm-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({lat: latlng.lat, lon: latlng.lon})
            })
            .then(response => response.json())
            .then(data => {
                console.log('Order confirmed:', data);
                userLocation = data.userLocation;
                if (routingControl) {
                    map.removeControl(routingControl);
                }
                routingControl = L.Routing.control({
                    waypoints: [
                        L.latLng(latlng.lat, latlng.lon), // Точка доставщика
                        L.latLng(userLocation.lat, userLocation.lon) // Точка пользователя
                    ],
                    routeWhileDragging: true
                }).addTo(map);
                startTrackingDelivery(data.deliveryId);
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        }

        map.on('locationfound', function(e) {
            placeMarker(e.latlng);
        });

        function startTrackingDelivery(deliveryId) {
            setInterval(() => {
                fetch(`/delivery-location/${deliveryId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Delivery location:', data);
                        if (deliveryMarker) {
                            map.removeLayer(deliveryMarker);
                        }
                        deliveryMarker = L.marker([data.lat, data.lon]).addTo(map);
                        map.setView([data.lat, data.lon], 13);
                        if (routingControl) {
                            routingControl.setWaypoints([
                                L.latLng(data.lat, data.lon), // Обновленная точка доставщика
                                L.latLng(userLocation.lat, userLocation.lon) // Точка пользователя
                            ]);
                        }
                    });
            }, 5000); // Обновление каждые 5 секунд
        }

        map.on('locationerror', function(e) {
            alert(e.message);
        });
    </script>
</body>
</html>
