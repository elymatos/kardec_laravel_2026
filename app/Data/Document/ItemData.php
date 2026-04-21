<?php

namespace App\Data\Document;

use Spatie\LaravelData\Data;

class ItemData extends Data
{
    public function __construct(
        public ?int $id = null
    ) {}
}
