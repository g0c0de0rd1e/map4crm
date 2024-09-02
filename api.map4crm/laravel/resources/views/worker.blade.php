<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .order-column {
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
    <input type="hidden" id="orderId" value="">
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // function fetchOrders() {
        //     $.get('/get-orders', function(data) {
        //         displayOrders(data);
        //     });
        // }
        
        function getCourierLocation(callback) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    callback(position.coords.latitude, position.coords.longitude);
                }, function(error) {
                    console.error('Error getting location:', error);
                    callback(null, null);
                });
            } else {
                console.error('Geolocation is not supported by this browser.');
                callback(null, null);
            }
        }

        function displayOrders(orders) {
            if (!Array.isArray(orders)) {
                console.error('Expected an array but got:', orders);
                return;
            }
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
                            ${order.status !== 'received' ? `<button class="btn btn-primary" onclick="updateOrderStatus(${order.id}, '${getNextStatus(order.status)}', ${order.lat}, ${order.lng})">Переместить в ${getNextStatus(order.status)}</button>` : ''}
                        </div>
                    </div>
                `;
                $(`#${order.status}`).append(orderDiv);

                if (order.status === 'on_the_way') {
                    $('#orderId').val(order.id); // Устанавливаем ID заказа, который в пути
                }
            });
        }

        function sendCourierCoordinates(orderId, lat, lng) {
            $.post('/save-courier-coordinates', {
                _token: '{{ csrf_token() }}',
                orderId: orderId,
                lat: lat,
                lng: lng
            })
            .done(function(data) {
                console.log('Courier coordinates saved:', data);
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Error saving courier coordinates:', textStatus, errorThrown);
                console.error('Response:', jqXHR.responseText);
            });
        }

        $(document).ready(function() {
            fetchOrders();
            setInterval(function() {
                var orderId = $('#orderId').val();
                if (orderId) {
                    getCourierLocation(function(lat, lng) {
                        if (lat && lng) {
                            sendCourierCoordinates(orderId, lat, lng);
                        }
                    });
                }
            }, 15000); // Обновление каждые 15 секунд
        });

        function getNextStatus(currentStatus) {
            switch (currentStatus) {
                case 'in_process':
                    return 'on_the_way';
                case 'on_the_way':
                    return 'received';
                case 'received':
                    return 'delete';
                default:
                    return 'in_process';
            }
        }

        $(document).ready(function() {
            var eventSource = new EventSource('/get-orders');

            eventSource.onmessage = function(event) {
                var orders = JSON.parse(event.data);
                displayOrders(orders);
            };

            eventSource.onerror = function(event) {
                console.error('EventSource failed:', event);
                eventSource.close();
            };
        });
    </script>
</head>
<body>
    <div class="container">
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
</body>
</html>
