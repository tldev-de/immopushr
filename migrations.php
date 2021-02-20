<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Utils\Migrations;

Migrations::add(1, function () {
    Capsule::schema()->create('offers', function ($table) {
        $table->increments('id');
        $table->string('provider');
        $table->string('foreign_id');
        $table->string('title');
        $table->string('price');
        $table->string('rooms');
        $table->string('flat_size');
        $table->string('address');
        $table->timestamps();
    });
});
