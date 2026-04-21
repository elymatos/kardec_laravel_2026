<x-layout.resource>
    <x-slot:title>
        Group/User
    </x-slot:title>
    <x-slot:actions>
        <x-button
            label="New Group"
            color="secondary"
            hx-get="/group/new"
            hx-target="#editArea"
            hx-swap="innerHTML"
        ></x-button>
        <x-button
            label="New User"
            color="secondary"
            hx-get="/user/new"
            hx-target="#editArea"
            hx-swap="innerHTML"
        ></x-button>
    </x-slot:actions>
    <x-slot:grid>
        <div
            hx-trigger="load"
            hx-target="this"
            hx-swap="outerHTML"
            hx-get="/user/grid"
        ></div>
    </x-slot:grid>
    <x-slot:edit>
        <div id="editArea">

        </div>
    </x-slot:edit>
</x-layout.resource>
