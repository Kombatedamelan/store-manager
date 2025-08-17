<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('storeName');
            $table->string('storePhone');
            $table->string('storeAddress');
            $table->string('currency')->default("FCFA");
            $table->string('logo')->nullable();
            $table->string('primaryColor')->nullable();
            $table->string('adminPhone')->unique();
            $table->string('adminPassword');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stores');
    }
}
