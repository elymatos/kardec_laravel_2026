<x-layout.resource>
    <x-slot:head>
{{--        <x-breadcrumb :sections="[['/','Home'],['','LU_candidate']]"></x-breadcrumb>--}}
    </x-slot:head>
    <x-slot:title>
        Item
    </x-slot:title>
    <x-slot:actions>
    </x-slot:actions>
    <x-slot:search>
        <x-form-search>
            <div class="field">
                <x-search-field
                    id="idItem"
                    placeholder="Search idItem"
                    hx-post="/items/grid/search"
                    hx-trigger="input changed delay:500ms, search"
                    hx-target="#itemTreeWrapper"
                    hx-swap="innerHTML"
                ></x-search-field>
            </div>
        </x-form-search>
    </x-slot:search>
    <x-slot:grid>
        <div
            hx-trigger="load"
            hx-target="this"
            hx-swap="outerHTML"
            hx-get="/items/grid"
        ></div>
    </x-slot:grid>
    <x-slot:edit>
        <div id="editArea">

        </div>
    </x-slot:edit>
</x-layout.resource>
