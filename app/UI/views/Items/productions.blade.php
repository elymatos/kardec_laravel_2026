<div
    id="gridProduction"
    class="grid"
    hx-trigger="reload-gridProduction from:body"
    hx-target="this"
    hx-swap="outerHTML"
    hx-get="/items/{{$idItem}}/production"
>
    @foreach($productions as $production)
        <div class="col-6">
            <div class="ui card w-full">
                <div class="content">
                    <span class="right floated">
                        <x-delete
                            title="delete Production"
                            onclick="manager.confirmDelete(`Removing Production '{{$production->idEntityRelation}}'.`, '/items/production/{{$production->idEntityRelation}}')"
                        ></x-delete>
                    </span>
                    <div
                        class="header"
                    >
                        {{$production->nameType}}
                    </div>
                </div>
                <div class="content">
                    {{$production->nameInstance}}
                </div>
            </div>
        </div>
    @endforeach
</div>


