@php
    use Orkester\Security\MAuth;
    $isLogged = MAuth::isLogged();
    $isFavorite = ($item->isFavorite) ? 1 : 0;
@endphp
@if($isLogged)
    <span
        id="favorite"
        class="ak-document-favorite"
        hx-post="/favorite/?id={{$item->idItem}}"
        hx-target="#favorite"
        hx-swap="outerHTML"
    ><i
        class="icon material"
        style="font-size:24px;font-variation-settings:'FILL' {{$isFavorite}},'wght' 400, 'GRAD' 0, 'opsz' 24;vertical-align: center; color:red;"
    >favorite
    </i>
    </span>
@endif
