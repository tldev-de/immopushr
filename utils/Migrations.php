<?php

namespace Utils;

use Illuminate\Database\Capsule\Manager as Capsule;
use Models\Migration;

class Migrations
{
    private static array $migrations;

    public static function add(int $id, callable $migration)
    {
        self::$migrations[$id] = $migration;
    }

    public static function run()
    {
        $database_file = Capsule::connection()->getConfig('database');
        if (!is_file($database_file)) {
            touch($database_file);
        }

        if (!Capsule::schema()->hasTable('migrations')) {
            Capsule::schema()->create('migrations', function ($table) {
                $table->id();
                $table->timestamps();
            });
        }

        foreach (self::$migrations as $id => $migration) {
            Migration::where('id', $id)->existsOr(function () use ($id, $migration) {
                $migration();
                $mig = new Migration();
                $mig->id = $id;
                $mig->updateTimestamps();
                $mig->save();
            });
        }
    }
}
