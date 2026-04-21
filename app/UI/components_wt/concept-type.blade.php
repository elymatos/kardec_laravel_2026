<div class="d-flex p-2">
    <input type="hidden" id="{{$id}}" name="{{$id}}" value="{{$value}}">
    <div x-data class="ui buttons">
        @foreach($options as $idOption => $option)
            <button
                type="button"
                class="ui mini button mr-2"
                @click="{{$id}}_onclick('{{$idOption}}')"
            >
                @php($component = "element.concept_" . $idOption)
                <x-dynamic-component :component="$component" :name="$option" />
            </button>
        @endforeach
        <button
            class="ui mini button mr-2"
            @click="{{$id}}_onclick('all')"
        >
            All
        </button>
    </div>
</div>
<script>
    $(function() {
        {{$id}}_onclick = function(value) {
            const field = document.getElementById('{{$id}}');
            field.value = value;
            field.dispatchEvent(new Event("typeChosen", { bubbles: true }));
        };

    });
</script>
