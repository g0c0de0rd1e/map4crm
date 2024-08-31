<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OSM Map with Laravel</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <div id="map" style="width: 100%; height: 500px;"></div>
    <input type="text" id="address" placeholder="Введите адрес">
    <button onclick="geocode()">Найти</button>
    <button onclick="saveAddress()">Сохранить адрес</button>

    <script>
        var map = L.map('map').setView([44.952117, 34.102417], 10); 
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var marker;
        var deliveryMarker;
        var routingControl;

        map.on('click', function(e) {
            if (marker) {
                map.removeLayer(marker);
            }
            marker = L.marker(e.latlng).addTo(map);
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
                .then(response => response.json())
                .then(data => {
                    console.log(data.display_name); 
                    marker.bindPopup(data.display_name).openPopup();
                    document.getElementById('address').value = data.display_name;
                });
        });

        function geocode() {
            var address = document.getElementById('address').value;
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${address}`)
                .then(response => response.json())
                .then(data => {
                    var latlng = [data[0].lat, data[0].lng];
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    marker = L.marker(latlng).addTo(map);
                    map.setView(latlng, 13);
                    marker.bindPopup(data[0].display_name).openPopup();
                });
        }

        function saveAddress() {
            var address = document.getElementById('address').value;
            if (marker) {
                var latlng = marker.getLatLng();
                $.post('/save-address', {
                    _token: '{{ csrf_token() }}',
                    address: address,
                    lat: latlng.lat,
                    lng: latlng.lng ,
                    status: 'in_process'
                })
                .done(function(data) {
                    console.log('Address saved:', data);
                    fetchOrders(); // Обновление таблицы заказов после сохранения адреса
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Error saving address:', textStatus, errorThrown);
                    console.error('Response:', jqXHR.responseText);
                });
            }
        }

        $(document).ready(function() {
            setInterval(function() {
                var orderId = $('#orderId').val();
                if (orderId) {
                    updateMapWithDeliveryMarker(orderId);
                }
            }, 15000); // Обновление каждые 15 секунд
        });

        function updateMapWithDeliveryMarker(id) {
            $.get(`/get-delivery-coordinates/${id}`, function(data) {
                if (deliveryMarker) {
                    map.removeLayer(deliveryMarker);
                }
                deliveryMarker = L.marker([data.latitude, data.longitude]).addTo(map);
                if (routingControl) {
                    routingControl.setWaypoints([
                        L.latLng(data.latitude, data.longitude),
                        L.latLng(marker.getLatLng().lat, marker.getLatLng().lng)
                    ]);
                } else {
                    routingControl = L.Routing.control({
                        waypoints: [
                            L.latLng(data.latitude, data.longitude),
                            L.latLng(marker.getLatLng().lat, marker.getLatLng().lng)
                        ],
                        routeWhileDragging: true
                    }).addTo(map);
                }
            });
        }

    </script>
</body>
</html>
