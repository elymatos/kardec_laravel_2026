@php
    use Seboettg\CiteProc\CiteProc;

    $style = "associacao-brasileira-de-normas-tecnicas.csl";

    $fileName = base_path() . "/data/citation-styles/bibtex.csl";
    $styleContent= file_get_contents($fileName);
    $citeBibTeX = new CiteProc($styleContent,"pt-BR");

    $options = [
        "acm-sig-proceedings.csl" => "ACM",
        "acs-nano.csl" => "ACS",
        "ama.csl" => "AMA",
        "apa.csl" => "APA",
        "associacao-brasileira-de-normas-tecnicas.csl" => "ABNT",
        "chicago-author-date.csl" => "Chicago",
        "harvard-cite-them-right.csl" => "Harvard",
        "ieee.csl" => "IEEE",
        "modern-language-association.csl" => "MLA",
        "turabian-fullnote-bibliography.csl" => "Turabian",
        "vancouver.csl" => "Vancouver",
    ];

//    $citeProcHtml = $citeProc->render($data, "bibliography");
//    $citeBibTeXHtml = $citeBibTeX->render($data, "bibliography");

@endphp
<div id="citation" class="ak-metadata-citation pt-2 pb-2">
    @include("Biography.citationDetail")
</div>


{{--{!! $citeBibTeX->render(json_decode($data), "bibliography") !!}--}}
<form class="ui form">
<div class="field">
    <x-combobox.options
            id="style"
            label="Formatos de citação"
            value="associacao-brasileira-de-normas-tecnicas.csl"
            :options="$options"
            class="w-15rem"
            hx-get="/biografias/item/{{$idItem}}/citation"
            hx-trigger="change"
            hx-target="#citation"
    ></x-combobox.options>
</div>
</form>
