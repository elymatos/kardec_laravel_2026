@php
    use Seboettg\CiteProc\CiteProc;

    $fileName = base_path() . "/data/citation-styles/{$style}";
    $styleContent= file_get_contents($fileName);
    $citeProc = new CiteProc($styleContent,"pt-BR");

    $author = [
        (object)[
            "family" => "Almeida",
            "given" => "Angélica A. Silva",
        ],
        (object)[
            "family" => "Bastos",
            "given" => "Carlos Seth",
        ]
    ];

    $extra = [200,287,342,197,345,344,343,203,204,346];
    if (in_array($idItem,$extra)) {
        $author[] = (object)[
            "family" => "Castro",
            "given" => "Nicolas Amaral de",
        ];
    }

    $data = [
        (object)[
        "author" => $author,
        "id" => $idItem,
        "issued" => (object)[
            "date-parts" => [
                [2020]
            ]
        ],
        "publisher-place" => "Projeto Allan Kardec",
        "title" => $itemBio->title,
        "type" => "article-newspaper",
        "URL" => "https://projetokardec.ufjf.br/biografias/item/$idItem}",
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
