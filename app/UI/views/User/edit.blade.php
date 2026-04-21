<x-layout.object :center="false">
    <x-slot:name>
        <span class="color_user">{{$user->login}}</span>
    </x-slot:name>
    <x-slot:detail>
        <div class="ui label tag wt-tag-id">
            #{{$user->idUser}}
        </div>
        <x-button
            label="Delete"
            color="danger"
            onclick="manager.confirmDelete(`Removing User '{{$user?->login}}'.`, '/user/{{$user->idUser}}')"
        ></x-button>
    </x-slot:detail>
    <x-slot:description>
    </x-slot:description>
    <x-slot:nav>
        <div class="ui vertical menu w-auto">
            <a
                class="item"
                hx-get="/user/{{$user->idUser}}/formEdit"
                hx-target="#objectMainArea"
            >
                Edit
            </a>
        </div>
    </x-slot:nav>
    <x-slot:main>
        <div id="objectMainArea" class="objectMainArea">
        </div>
    </x-slot:main>
</x-layout.object>
