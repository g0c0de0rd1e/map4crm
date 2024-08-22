<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Orders Management</h1>
        <div class="row">
            <div class="col">
                <h2>В обработке</h2>
                <div id="in_process" class="order-column"></div>
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
    </div>

    <script>
        function fetchOrders() {
            $.get('/get-orders', function(data) {
                displayOrders(data);
            });
        }

        function displayOrders(orders) {
            $('#in_process').empty();
            $('#on_the_way').empty();
            $('#received').empty();

            orders.forEach(order => {
                var orderDiv = `
                    <div class="card mb-2">
                        <div class="card-body">
                            <p>Заказ ID: ${order.id}</p>
                            <p>Адрес: ${order.address}</p>
                            <p>Статус: ${order.status}</p>
                            ${order.status !== 'received' ? `<button class="btn btn-primary" onclick="updateOrderStatus(${order.id}, '${getNextStatus(order.status)}')">Переместить в ${getNextStatus(order.status)}</button>` : ''}
                        </div>
                    </div>
                `;
                $(`#${order.status}`).append(orderDiv);
            });
        }

        function getNextStatus(currentStatus) {
            if (currentStatus === 'in_process') return 'on_the_way';
            if (currentStatus === 'on_the_way') return 'received';
            return '';
        }

        function updateOrderStatus(id, status) {
            $.post(`/update-order-status/${id}`, {
                _token: '{{ csrf_token() }}',
                status: status
            }, function(data) {
                fetchOrders();
                if (status === 'on_the_way') {
                    updateMapWithDeliveryMarker(id);
                }
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
            fetchOrders();
            setInterval(fetchOrders, 5000); // Обновление каждые 5 секунд
        });
    </script>
</body>
</html>