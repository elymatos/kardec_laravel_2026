<?php

namespace App\Data\Item;

use Spatie\LaravelData\Data;

class UpdateMetadataData extends Data
{
    public function __construct(
        public int $idItem,
        public string $type,
        public string $instance
    ) {}
}
