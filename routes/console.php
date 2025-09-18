<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register the SQL import command
Artisan::command('db:import-sql {--fresh} {--confirm}', function () {
    return $this->call(\App\Console\Commands\ImportSqlData::class, [
        '--fresh' => $this->option('fresh'),
        '--confirm' => $this->option('confirm'),
    ]);
})->purpose('Import data from sql_injections.sql file');
