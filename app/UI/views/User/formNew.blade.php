<x-form id="formNew" title="New User" :center="false"  hx-post="/user/new">
    <x-slot:fields>
        <section class="hxRow">
            <section class="hxCol hxSpan-6">
                <x-text-field
                    label="Login"
                    id="login"
                    value=""
                ></x-text-field>
            </section>
            <section class="hxCol hxSpan-6">
                <x-text-field
                    label="Email"
                    id="email"
                    value=""
                ></x-text-field>
            </section>
        </section>
        <x-text-field
            label="Name"
            id="name"
            value=""
        ></x-text-field>
        <x-combobox.group
            id="idGroup"
            label="Group"
        ></x-combobox.group>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Save"></x-submit>
    </x-slot:buttons>
</x-form>
