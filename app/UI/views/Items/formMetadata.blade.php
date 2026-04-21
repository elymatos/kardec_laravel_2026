@php
    $id = uniqid("formItemMetadata");
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
                    <div class="field mr-1">
                        <x-combobox.metadata-type
                            id="type"
                            label="Type"
                            style="width:250px"
                            class="mb-2"
                            value=""
                            onChange="htmx.ajax('GET','/items/metadata/instance/' + value,'#instances');"
                        ></x-combobox.metadata-type>
                    </div>
                    <div id="instances" class="field w-15rem mr-1">
                        <x-combobox.metadata-instance
                            id="instance"
                            label="Instance"
                            value=""
                            :nameType="$metadata?->nameType ?? 0"
                            :hasNull="false"
                        ></x-combobox.metadata-instance>
                    </div>
                </div>
            </x-slot:fields>
            <x-slot:buttons>
                <x-button
                    label="Update Metadata"
                    hx-put="/items/metadata"
                ></x-button>
            </x-slot:buttons>
        </x-form>
    </div>
    <div class="flex-grow-1">
        @include("Items.metadata")
    </div>
</div>

