<div {{$attributes}}>
    <div class="form-field field" style="overflow:initial">
        <label for="{{$id}}">{{$label}}</label>
        <div id="{{$id}}_dropdown" class="ui tiny selection dropdown" style="overflow:initial">
            <input type="hidden" id="{{$id}}" name="{{$id}}" value="{{$value}}">
            <i class="dropdown icon"></i>
            <div class="default text">Concept Type</div>
            <div class="menu">
                @foreach($options as $idOption => $option)
                    <div data-value="{{$idOption}}"
                         class="item p-1 min-h-0"
                    >
                        {{$option}}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<script>
    $(function() {
        $('#{{$id}}_dropdown').dropdown({
            onChange: function(value, text, $choice) {
                const field = document.getElementById('{{$id}}');
                field.value = value;
                field.dispatchEvent(new Event('typeChanged', { bubbles: true }));
            }
        })

    });
</script>
