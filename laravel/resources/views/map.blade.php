<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OSM Map with Laravel</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
</head>
<body>
    <div id="map" style="width: 100%; height: 500px;"></div>
    <input type="text" id="address" placeholder="Введите адрес">
    <button onclick="geocode()">Найти</button>

    <script>
        var map = L.map('map').setView([44.952117, 34.102417], 10); // Центр карты в Крыму
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var marker;

        map.on('click', function(e) {
            if (marker) {
                map.removeLayer(marker);
            }
            marker = L.marker(e.latlng).addTo(map);
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
                .then(response => response.json())
                .then(data => {
                    console.log(data.display_name); // Вывод адреса в консоль
                    marker.bindPopup(data.display_name).openPopup();
                    sendJson({lat: e.latlng.lat, lon: e.latlng.lng, address: data.display_name});
                });
        });

        function geocode() {
            var address = document.getElementById('address').value;
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${address}`)
                .then(response => response.json())
                .then(data => {
                    var latlng = [data[0].lat, data[0].lon];
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    marker = L.marker(latlng).addTo(map);
                    map.setView(latlng, 13);
                    marker.bindPopup(data[0].display_name).openPopup();
                    sendJson({lat: data[0].lat, lon: data[0].lon, address: data[0].display_name});
                });
        }

        function sendJson(data) {
            fetch('/save-location', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        }
    </script>
</body>
</html>
