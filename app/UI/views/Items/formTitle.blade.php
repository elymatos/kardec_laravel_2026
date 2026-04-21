@php
    $id = uniqid("formEditItemTitle");
@endphp
<x-form
    id="{{$id}}"
>
    <x-slot:fields>
        <x-hidden-field id="idItem" :value="$item->idItem"></x-hidden-field>
        <div class="field">
            <x-text-field
                label="Title (pt)"
                id="ptTitle"
                :value="$item->ptTitle ?? ''"
            ></x-text-field>
        </div>
        <div class="field">
            <x-text-field
                label="Title (fr)"
                id="frTitle"
                :value="$item->frTitle ?? ''"
            ></x-text-field>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-button
            label="Update Title"
            hx-put="/items"
        ></x-button>
    </x-slot:buttons>
</x-form>

