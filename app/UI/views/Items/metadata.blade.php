<div
    id="gridMetadata"
    class="grid"
    hx-trigger="reload-gridMetadata from:body"
    hx-target="this"
    hx-swap="outerHTML"
    hx-get="/items/{{$idItem}}/metadata"
>
    @foreach($metadatas as $metadata)
        <div class="col-6">
            <div class="ui card w-full">
                <div class="content">
                    <span class="right floated">
                        <x-delete
                            title="delete Metadata"
                            onclick="manager.confirmDelete(`Removing Metadata '{{$metadata->idEntityRelation}}'.`, '/items/metadata/{{$metadata->idEntityRelation}}')"
                        ></x-delete>
                    </span>
                    <div
                        class="header"
                    >
                        {{$metadata->nameType}}
                    </div>
                </div>
                <div class="content">
                    {{$metadata->nameInstance}}
                </div>
            </div>
        </div>
    @endforeach
</div>


