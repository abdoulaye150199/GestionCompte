<?php<?php

require __DIR__ . "/vendor/autoload.php";require __DIR__ . "/vendor/autoload.php";

$app = require_once __DIR__ . "/bootstrap/app.php";$app = require_once __DIR__ . "/bootstrap/app.php";

$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();



$cols = DB::select("$schema = DB::connection()->getDoctrineSchemaManager();

    SELECT column_name, data_type, character_maximum_length $table = $schema->listTableDetails("comptes");

    FROM information_schema.columns print_r($table->getColumns());

    WHERE table_name = 'comptes'

");
print_r($cols);