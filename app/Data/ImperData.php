<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ImperData extends Data
{
    public function __construct(
        public int $idUser,
        public string $password
    ) {}

}
