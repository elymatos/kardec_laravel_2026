<?php

namespace App\Data\Timeline;

use Spatie\LaravelData\Data;

class UpdateData extends Data
{
    public function __construct(
        public ?array $subgroup = []
    ) {}
}
