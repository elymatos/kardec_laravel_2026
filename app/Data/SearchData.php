<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class SearchData extends Data
{
    public function __construct(
        public ?string $search = '',
        public ?int $idItem = null,
        public ?string $collectionCode = '',
        public ?string $year = '',
        public ?string $idTag = '',
        public ?string $metadataType = null,
        public ?int $metadataInstanceId = null,
    ) {}

}
