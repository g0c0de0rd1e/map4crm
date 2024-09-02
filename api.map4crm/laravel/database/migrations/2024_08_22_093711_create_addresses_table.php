<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id'); // Добавляем внешний ключ для номера заказа
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('deliveries')->onDelete('cascade'); // Внешний ключ для номера заказа
        });
    }

    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
