
TY  - {{$citationData->risType}}
@foreach($citationData->author as $author)
AU  - {{$author->family}}, {{$author->given}}
@endforeach
@if( $citationData->risType === 'BOOK')
@if( $citationData->$collectionEditor)
A2  - {{$citationData->$collectionEditor}}
@endif
@foreach from=$citationData->editor item="editor"}}
A3  - {{$editor->family}}, {{$editor->given}}
@endforeach
@elseif ($citationData->risType === 'CHAP')
@foreach from=$citationData->editor item="editor"}}
A2  - {{$editor->family}}, {{$editor->given}}
@endforeach
@if( $citationData->$collectionEditor)
A3  - {{$citationData->$collectionEditor}}
@endif
@endif
@foreach($citationData->translator as $translator)
A4  - {{$translator->family}}, {{$translator->given}}
@endforeach
@if( $citationData->title)
TI  - {{$citationData->title}}
@endif
@if( $citationData->risType === 'JOUR')
@if( $citationData->issued)
PY  - {{$citationData->issued->raw ?? date_format:"%Y/%m/%d"}}
@endif
@if( $citationData->accessed)
Y2  - {{$citationData->accessed->raw ?? date_format:"%Y/%m/%d"}}
@endif
@if( $citationData->$containerTitle)
JF  - {{$citationData->$containerTitle}}
@endif
@if( $citationData->$containerTitleShort)
JA  - {{$citationData->$containerTitleShort}}
@endif
@if( $citationData->volume)
VL  - {{$citationData->volume}}
@endif
@if( $citationData->issue)
IS  - {{$citationData->issue}}
@endif
@if( $citationData->section)
SE  - {{$citationData->section}}
@endif
@else
@if( $citationData->$containerTitle)
T2  - {{trim($citationData->$containerTitle)}}
@endif
@if( $citationData->$collectionTitle)
T3  - {{trim($citationData->$collectionTitle)}}
@endif
@if( $citationData->volume)
M1  - {{$citationData->volume}}
@endif
@if( $citationData->$publisherPlace)
PP  - {{$citationData->$publisherPlace}}
@endif
@if( $citationData->$publisher)
PB  - {{$citationData->publisher}}
@endif
@if( $citationData->issued)
PY  - {{$citationData->issued->raw ?? date_format:"%Y"}}
@endif
@endif
@foreach($citationData->languages as $language)
LA  - {{$language}}
@endforeach
@foreach($citationData->serialNumber as $serialNumber)
SN  - {{$serialNumber}}
@endforeach
@foreach($citationData->keywords as $keyword)
KW  - {{$keyword}}
@endforeach
@if( $citationData->DOI)
DO  - {{$citationData->DOI}}
UR  - https://doi.org/{{$citationData->DOI}}
{{else}}
UR  - {{$citationData->URL}}
@endif
@if( $citationData->page)
SP  - {{$citationData->page}}
@endif
@if( $citationData->abstract)
AB  - {{$citationData->abstract ?? replace:"\r\n":""|replace:"\n":""}}
@endif
ER  -
