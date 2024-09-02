<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Map</title>
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
        var map = L.map('map', {
            attributionControl: false // Убираем легенду карты
        }).setView([{{ $delivery->lat }}, {{ $delivery->lng }}], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var userMarker = L.marker([{{ $delivery->lat }}, {{ $delivery->lng }}]).addTo(map).bindPopup("User Location").openPopup();
        var deliveryMarker;
        var routingControl;

        // Запрещаем ставить метки на карте
        map.on('click', function(e) {
            return false;
        });
        function updateMapWithDeliveryMarker(id) {
            $.get(`/get-delivery-coordinates/${id}`, function(data, status) {
                if (status === '204') {
                    console.log('Order completed or coordinates unchanged');
                    return;
                }

                // Проверяем, что координаты курьера отличаются от координат пользователя
                if (data.latitude !== {{ $delivery->lat }} || data.longitude !== {{ $delivery->lng }}) {
                    if (deliveryMarker) {
                        map.removeLayer(deliveryMarker);
                    }
                    deliveryMarker = L.marker([data.latitude, data.longitude]).addTo(map).bindPopup("Courier Location").openPopup();
                    if (routingControl) {
                        routingControl.setWaypoints([
                            L.latLng(data.latitude, data.longitude),
                            L.latLng({{ $delivery->lat }}, {{ $delivery->lng }})
                        ]);
                    } else {
                        routingControl = L.Routing.control({
                            waypoints: [
                                L.latLng(data.latitude, data.longitude),
                                L.latLng({{ $delivery->lat }}, {{ $delivery->lng }})
                            ],
                            routeWhileDragging: true,
                            createMarker: function() { return null; }, // Убираем маркеры маршрута
                        }).addTo(map);
                    }
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Error fetching delivery coordinates:', textStatus, errorThrown);
                console.error('Response:', jqXHR.responseText);
            });
        }

        $(document).ready(function() {
            // Загрузка меток при загрузке страницы
            updateMapWithDeliveryMarker({{ $delivery->id }});

            setInterval(function() {
                updateMapWithDeliveryMarker({{ $delivery->id }});
            }, 15000); // Обновление каждые 15 секунд
        });

    </script>
</body>
</html>
