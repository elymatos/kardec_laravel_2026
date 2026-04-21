<?php

namespace App\Data\Item;

use Spatie\LaravelData\Data;

class SearchData extends Data
{
    public function __construct(
        public ?int $idItem = null,
    ) {}

}
