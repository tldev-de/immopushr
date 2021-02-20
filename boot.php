<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Env;
use Utils\Migrations;

require 'vendor/autoload.php';

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null): mixed
    {
        return Env::get($key, $default);
    }
}

// read .env file
if (is_file(__DIR__ . DIRECTORY_SEPARATOR . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// initialize database
$database = env('DB_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'immopushr.sqlite');
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => $database,
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

## run migrations
require_once 'migrations.php';
Migrations::run();

## explicitly load providers
$files = glob(__DIR__ . DIRECTORY_SEPARATOR . 'provider' . DIRECTORY_SEPARATOR . '*.php');
foreach ($files as $file) {
    require_once($file);
}
