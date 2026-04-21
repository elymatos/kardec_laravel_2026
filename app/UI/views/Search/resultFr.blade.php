<table>
    @foreach($results as $idItem => $sentences)
        <tr>
            <td
            >
                <div class="ak-static-link">
                    <a
                        href="/item-fr?id={{$sentences[0]->idItem}}"
                    >[{{$sentences[0]->idItem}}] {{$sentences[0]->title}}
                    </a>  [{{$sentences[0]->docDate}}] [{{$sentences[0]->collection}}] [
                    @foreach($sentences[0]->tags as $tag)
                        {{$tag->ptName}}
                    @endforeach
                    ]
                </div>
            </td>
        </tr>
        @foreach($sentences as $sentence)
            <tr>
                <td>
                    <div class="pl-4 pb-3 text-sm">{!! $sentence->text !!}</div>
                </td>
            </tr>
        @endforeach
    @endforeach
</table>
