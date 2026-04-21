<?php

use App\Providers\AppServiceProvider;
use App\Providers\Neo4jServiceProvider;
use Laravel\Boost\BoostServiceProvider;

return [
    AppServiceProvider::class,
    Neo4jServiceProvider::class,
    BoostServiceProvider::class,
];
