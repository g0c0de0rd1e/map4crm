<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        .order-column, .address-column {
            max-height: 400px;
            overflow-y: auto;
        }
        .address-column {
            max-width: 300px;
        }
        .btn {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col">
                <h2>В обработке</h2>
                <div id="user_addresses" class="order-column"></div>
            </div>
            <div class="col">
                <h2>В пути</h2>
                <div id="on_the_way" class="order-column"></div>
            </div>
            <div class="col">
                <h2>Получен</h2>
                <div id="received" class="order-column"></div>
            </div>
        </div>
        <!-- <div class="row mt-4">
            <div class="col">
                <h2>Адреса пользователя</h2>
                <div id="user_addresses" class="address-column"></div>
            </div>
        </div> -->
    </div>

    <script>
        function fetchUserAddresses() {
            $.get('/user-addresses', function(data) {
                displayAddresses(data);
            });
        }

        function displayAddresses(addresses) {
            $('#user_addresses').empty();

            addresses.forEach(address => {
                var addressDiv = `
                    <div class="card mb-2">
                        <div class="card-body">
                            <p>Адрес: ${address.address}</p>
                            <p>Широта: ${address.lat}</p>
                            <p>Долгота: ${address.lng}</p>
                        </div>
                    </div>
                `;
                $('#user_addresses').append(addressDiv);
            });
        }

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

        $(document).ready(function() {
            fetchUserAddresses();
            setInterval(fetchUserAddresses, 500); // Обновление адресов каждые 5 секунд
        });
    </script>
</body>
</html>
