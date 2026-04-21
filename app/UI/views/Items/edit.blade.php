@php use Carbon\Carbon; @endphp
<x-layout.object>
    <x-slot:name>
        <span>#{{$item->idItem}}</span>
    </x-slot:name>
    <x-slot:detail>
        <div class="ui label tag wt-tag-id">
            #{{$item->idItem}}
        </div>
    </x-slot:detail>
    <x-slot:description>
        {{$item->docIndex}}
    </x-slot:description>
    <x-slot:main>
        @include("Items.menu")
    </x-slot:main>
</x-layout.object>
