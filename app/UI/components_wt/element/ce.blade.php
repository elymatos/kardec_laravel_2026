{{--<span style="display:inline-block;padding:0px 4px;" {{$attributes->merge(['class' => 'color_'. $idColor])}}>--}}
{{--    <span class="inline-block"><i class="{{$icon}} icon" style="visibility: visible;font-size:0.875em"></i>{{$name}}</span>--}}
{{--</span>--}}
@php
//    $icon = $icon ?? config("webtool.fe.icon")[$type]
@endphp
<div class="d-flex justify-left items-center">
{{--    <div><i class="{{$icon}} icon"></i></div>--}}
    <div class="color_{{$idColor}}" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;padding: 0 2px;">{{$name}}</div>
</div>
