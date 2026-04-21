<x-layout.main>
    <x-slot:title>
        {{__("pk.profile")}}
    </x-slot:title>
    <x-slot:actions>
    </x-slot:actions>
    <x-slot:main>
        <x-form id="formProfile" :center="false" hx-post="/user/profile">
            <x-slot:fields>
                <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                <x-hidden-field
                    id="idUser"
                    :value="$user->idUser"
                ></x-hidden-field>
{{--                <x-text-field--}}
{{--                    label="Email"--}}
{{--                    id="email"--}}
{{--                    :value="$user->email"--}}
{{--                ></x-text-field>--}}
                <x-text-field
                    :label="__('pk.form.profile.name')"
                    id="name"
                    :value="$user->name"
                ></x-text-field>
            </x-slot:fields>
            <x-slot:buttons>
                <x-submit
                    :label="__('pk.form.profile.save')"
                ></x-submit>
            </x-slot:buttons>
        </x-form>
    </x-slot:main>
</x-layout.main>


