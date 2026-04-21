<x-form id="formEdit" title="User" :center="false" hx-post="/user">
    <x-slot:fields>
        <x-hidden-field
            id="idUser"
            :value="$user->idUser"
        ></x-hidden-field>
        <x-text-field
            label="Email"
            id="email"
            :value="$user->email"
        ></x-text-field>
        <x-text-field
            label="Name"
            id="name"
            :value="$user->name"
        ></x-text-field>
        <x-combobox.group
            id="idGroup"
            label="Group"
            :value="$user->groups[0]->idGroup"
        ></x-combobox.group>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Save" ></x-submit>
    </x-slot:buttons>
</x-form>
