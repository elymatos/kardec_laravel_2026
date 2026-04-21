<?php

namespace App\Data\Item;

use Spatie\LaravelData\Data;

class UpdateTitleData extends Data
{
    public function __construct(
        public int $idItem,
        public ?string $ptTitle = '',
        public ?string $frTitle = '',
    ) {}
}
