<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <style>
        .leaflet-routing-container {
            display: none;
        }
    </style>

    <div id="map" style="width: 100%; height: 500px;"></div>

    <script>
        var map = L.map('map').setView([0, 0], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var userMarker;
        var deliveryMarker;
        var routingControl;

        // Запрещаем ставить метки на карте
        map.on('click', function(e) {
            return false;
        });

        function loadUserMarker(orderId) {
            $.get(`/get-user-location-by-order/${orderId}`)
                .done(function(userLocation) {
                    console.log('User Location:', userLocation);

                    if (userMarker) {
                        userMarker.setLatLng([userLocation.lat, userLocation.lng]);
                    } else {
                        userMarker = L.marker([userLocation.lat, userLocation.lng]).addTo(map).bindPopup("User Location").openPopup();
                        map.setView([userLocation.lat, userLocation.lng], 13);
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Error fetching user location:', textStatus, errorThrown);
                    console.error('Response:', jqXHR.responseText);
                });
        }

        function updateCourierMarker(orderId) {
            $.get(`/get-delivery-coordinates/${orderId}`)
                .done(function(deliveryCoordinates) {
                    console.log('Delivery Coordinates:', deliveryCoordinates);

                    if (deliveryMarker) {
                        deliveryMarker.setLatLng([deliveryCoordinates.latitude, deliveryCoordinates.longitude]);
                    } else {
                        deliveryMarker = L.marker([deliveryCoordinates.latitude, deliveryCoordinates.longitude]).addTo(map).bindPopup("Courier Location").openPopup();
                    }

                    if (userMarker) {
                        if (routingControl) {
                            routingControl.setWaypoints([
                                L.latLng(deliveryCoordinates.latitude, deliveryCoordinates.longitude),
                                L.latLng(userMarker.getLatLng().lat, userMarker.getLatLng().lng)
                            ]);
                        } else {
                            routingControl = L.Routing.control({
                                waypoints: [
                                    L.latLng(deliveryCoordinates.latitude, deliveryCoordinates.longitude),
                                    L.latLng(userMarker.getLatLng().lat, userMarker.getLatLng().lng)
                                ],
                                routeWhileDragging: true,
                                createMarker: function() { return null; }, // Убираем маркеры маршрута
                            }).addTo(map);
                        }
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Error fetching delivery coordinates:', textStatus, errorThrown);
                    console.error('Response:', jqXHR.responseText);
                });
        }

        $(document).ready(function() {
            var orderId = {{$delivery->id}};
            loadUserMarker(orderId);

            // Загрузка метки курьера при загрузке страницы
            updateCourierMarker(orderId);

            setInterval(function() {
                updateCourierMarker(orderId);
            }, 15000); // Обновление каждые 15 секунд
        });
    </script>
</body>
</html>