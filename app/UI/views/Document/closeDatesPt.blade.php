<table class="k-doc-around">
    @foreach($item->around as $itemAround)
        <tr>
            <td><a href="/item-pt?id={{ $itemAround->id }}">#{{ $itemAround->id }}</a></td>
            <td>{{ $itemAround->date }}</td>
            <td>{{ $itemAround->title }}</td>
        </tr>
    @endforeach
</table>
