@php
$items = [
    ['formTitle','Title'],
    ['formProduction','Production'],
    ['formMetadata','Metadata'],
];
$id = uniqid($item->idItem);
@endphp
<x-objectmenu
    id="itemMenu_{{$id}}"
    :items="$items"
    :path="'items/' . $item->idItem"
></x-objectmenu>
