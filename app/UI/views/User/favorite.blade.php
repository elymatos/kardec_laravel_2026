<x-layout.main>
    <x-slot:title>
        {{__("pk.favorite")}}
    </x-slot:title>
    <x-slot:actions>
    </x-slot:actions>
    <x-slot:main>
        <table
            class="ak-favorites"
        >
            @foreach($favorites as $favorite)
                <tr>
                    <td
                        class="cursor-pointer ak-static-link ak-access-item"
                    ><a
                            href="/item-{{$locale}}?id={{$favorite->idItem}}"
                        >
                            [{{$favorite->idItem}}]
                        </a>
                    </td>
                    <td>{{$favorite->date}}</td>
                    <td>{!! ($locale == 'pt') ? $favorite->ptTitle : $favorite->frTitle !!}</td>
                </tr>
            @endforeach
        </table>
    </x-slot:main>
</x-layout.main>


