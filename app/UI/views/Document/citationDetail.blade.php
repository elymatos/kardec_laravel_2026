@php
    use Seboettg\CiteProc\CiteProc;

    $fileName = base_path() . "/data/citation-styles/{$style}";
    $styleContent= file_get_contents($fileName);
    $citeProc = new CiteProc($styleContent,"pt-BR");

    $data = [
        (object)[
        "author" => [
            (object)[
//            "family" => " ",
            "given" => "Projeto Allan Kardec",
//            "suffix" => " "
            ]
        ],
        "id" => $item->idItem,
        "issued" => (object)[
            "date-parts" => [
                [substr($item->dtPublished,6,4),substr($item->dtPublished,4,2),substr($item->dtPublished,0,2)]
            ]
        ],
        "publisher-place" => "Projeto Allan Kardec",
        "title" => $item->title . " [{$item->docDate}]",
        "type" => "article-newspaper",
        "URL" => "https://projetokardec.ufjf.br/item-pt?id={$item->idItem}",
        "accessed"=> (object)[
            "date-parts" => [
                [date("Y"),date("m"),date("d")]
            ]
        ]
        ]
    ];
    $citeProcHtml = $citeProc->render($data, "bibliography");

@endphp
    {!! $citeProcHtml !!}
