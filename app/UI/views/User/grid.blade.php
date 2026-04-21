<div
    class="wt-datagrid flex flex-column"
    style="height:100%"
    hx-trigger="reload-gridUser from:body"
    hx-target="this"
    hx-swap="outerHTML"
    hx-get="/user/grid"
>
{{--    <div class="datagrid-header">--}}
{{--        <div class="datagrid-title">--}}
{{--            Group/User--}}
{{--        </div>--}}
{{--    </div>--}}
    <div class="datagrid-header-search flex">
        <div style="padding:4px 0px 4px 4px">
            <x-search-field
                id="group"
                placeholder="Search Group"
                hx-post="/user/grid/search"
                hx-trigger="input changed delay:500ms, search"
                hx-target="#gridUser"
                hx-swap="innerHTML"
            ></x-search-field>
        </div>
        <div style="padding:4px 0px 4px 4px">
            <x-search-field
                id="user"
                placeholder="Search Login/Email/Name"
                hx-post="/user/grid/search"
                hx-trigger="input changed delay:500ms, search"
                hx-target="#gridUser"
                hx-swap="innerHTML"
            ></x-search-field>
        </div>
    </div>
    <div class="table" style="position:relative;height:100%">
        <table id="gridUser">
            <tbody
            >
            @fragment('search')
                @foreach($groups as $idGroup => $group)
                    <tr
                        hx-target="#editArea"
                        hx-swap="innerHTML"
                        class="subheader"
                    >
                        <td
                            hx-get="/group/{{$idGroup}}/edit"
                            class="cursor-pointer"
                            style="min-width:120px"
                            colspan="3"
                        >
                            <span class="text-blue-900 font-bold">{{$group->name}}</span>
                        </td>
                    </tr>
                    @foreach($users[$idGroup] as $user)
                        <tr
                            hx-target="#editArea"
                            hx-swap="innerHTML"
                        >
                            <td
                                hx-get="/user/{{$user->idUser}}/edit"
                                class="cursor-pointer"
                                style="min-width:120px"
                            >
                                <span>{{$user->login}}</span>
                            </td>
                            <td
                                hx-get="/user/{{$user->idUser}}/edit"
                                class="cursor-pointer"
                                style="min-width:120px"
                            >
                                <span>{{$user->name}}</span>
                            </td>
                            <td>
                                @if($user->status == 'pending')
                                    <button
                                        class="wt-button small wt-button-danger"
                                        hx-put="/user/{{$user->idUser}}/authorize"
                                    >
                                        authorize
                                    </button>

                                @else
                                    {{$user->status}}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @endfragment
            </tbody>
        </table>
    </div>
</div>

{{--<x-datagrid--}}
{{--    id="gridUser"--}}
{{--    title="Group/User"--}}
{{--    type="child"--}}
{{--    hx-trigger="reload-gridUser from:body"--}}
{{--    hx-target="this"--}}
{{--    hx-swap="outerHTML"--}}
{{--    hx-get="/user/grid"--}}
{{--    class="h-full"--}}
{{-->--}}
{{--    <x-slot:thead>--}}
{{--        <thead>--}}
{{--        <th style="padding:4px 0px 4px 4px">--}}
{{--            <x-search-field--}}
{{--                id="group"--}}
{{--                placeholder="Search Group"--}}
{{--                hx-post="/user/grid/search"--}}
{{--                hx-trigger="input changed delay:500ms, search"--}}
{{--                hx-target="#gridUser"--}}
{{--                hx-swap="innerHTML"--}}
{{--            ></x-search-field>--}}
{{--        </th>--}}
{{--        <th style="padding:4px 0px 4px 4px">--}}
{{--            <x-search-field--}}
{{--                id="user"--}}
{{--                placeholder="Search Login/Email/Name"--}}
{{--                hx-post="/user/grid/search"--}}
{{--                hx-trigger="input changed delay:500ms, search"--}}
{{--                hx-target="#gridUser"--}}
{{--                hx-swap="innerHTML"--}}
{{--            ></x-search-field>--}}
{{--        </th>--}}
{{--        <th></th>--}}
{{--        </thead>--}}
{{--    </x-slot:thead>--}}
{{--    @fragment('search')--}}
{{--        @foreach($groups as $idGroup => $group)--}}
{{--            <tr--}}
{{--                hx-target="#editArea"--}}
{{--                hx-swap="innerHTML"--}}
{{--            >--}}
{{--                <td--}}
{{--                    hx-get="/group/{{$idGroup}}/edit"--}}
{{--                    class="cursor-pointer"--}}
{{--                    style="min-width:120px"--}}
{{--                >--}}
{{--                    <span>{{$group->name}}</span>--}}
{{--                </td>--}}
{{--                <td>--}}

{{--                </td>--}}
{{--                <td></td>--}}
{{--                <td></td>--}}
{{--            </tr>--}}
{{--            @foreach($users[$idGroup] as $user)--}}
{{--                <tr--}}
{{--                    hx-target="#editArea"--}}
{{--                    hx-swap="innerHTML"--}}
{{--                >--}}
{{--                    <td></td>--}}
{{--                    <td--}}
{{--                        hx-get="/user/{{$user->idUser}}/edit"--}}
{{--                        class="cursor-pointer"--}}
{{--                        style="min-width:120px"--}}
{{--                    >--}}
{{--                        <span>{{$user->login}}</span>--}}
{{--                    </td>--}}
{{--                    <td--}}
{{--                        hx-get="/user/{{$user->idUser}}/edit"--}}
{{--                        class="cursor-pointer"--}}
{{--                        style="min-width:120px"--}}
{{--                    >--}}
{{--                        <span>{{$user->name}}</span>--}}
{{--                    </td>--}}
{{--                    <td>--}}
{{--                        @if($user->status == 'pending')--}}
{{--                            <button--}}
{{--                                class="wt-button small wt-button-danger"--}}
{{--                                hx-put="/user/{{$user->idUser}}/authorize"--}}
{{--                            >--}}
{{--                                authorize--}}
{{--                            </button>--}}

{{--                        @else--}}
{{--                            {{$user->status}}--}}
{{--                        @endif--}}
{{--                    </td>--}}
{{--                </tr>--}}
{{--            @endforeach--}}
{{--        @endforeach--}}
{{--    @endfragment--}}
{{--</x-datagrid>--}}
