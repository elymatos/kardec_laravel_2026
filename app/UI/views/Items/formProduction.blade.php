@php
    $id = uniqid("formItemProduction");
@endphp
<div
    class="flex gap-2"
>
    <div>
        <x-form
            id="{{$id}}"

        >
            <x-slot:fields>
                <x-hidden-field id="idItem" :value="$item->idItem"></x-hidden-field>
                <div class="fields">
                    <div class="field">
                        <x-combobox.production-type
                            label="Production"
                            id="type"
                            value=""
                        ></x-combobox.production-type>
                    </div>
                    <div class="field">
                        <x-combobox.team
                            label="Team member"
                            id="instance"
                            value=""
                        ></x-combobox.team>
                    </div>
                </div>
            </x-slot:fields>
            <x-slot:buttons>
                <x-button
                    label="Update Production"
                    hx-put="/items/production"
                ></x-button>
            </x-slot:buttons>
        </x-form>
    </div>
    <div class="flex-grow-1">
        @include("Items.productions")
    </div>
</div>

