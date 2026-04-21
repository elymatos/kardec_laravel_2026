<p class="k-doc-meta-section">Detalhes</p>

<div class="k-doc-meta-item">
    <div class="k-doc-meta-key">{{ __('pk.title') }}</div>
    <div class="k-doc-meta-val">{{ $item->title }}</div>
</div>
<div class="k-doc-meta-item">
    <div class="k-doc-meta-key">{{ __('pk.date') }}</div>
    <div class="k-doc-meta-val">{{ $item->docDate }}</div>
</div>
<div class="k-doc-meta-item">
    <div class="k-doc-meta-key">{{ __('pk.index') }}</div>
    <div class="k-doc-meta-val">{{ $item->docIndex }}</div>
</div>
<div class="k-doc-meta-item">
    <div class="k-doc-meta-key">{{ __('pk.collection') }}</div>
    <div class="k-doc-meta-val">{{ $item->collection }}</div>
</div>
@foreach($item->metadata as $type => $instance)
    <div class="k-doc-meta-item">
        <div class="k-doc-meta-key">{{ __("pk.{$type}") }}</div>
        <div class="k-doc-meta-val">{{ $instance }}</div>
    </div>
@endforeach

<p class="k-doc-meta-section">Produção</p>

@if(isset($item->production['translation']))
    <div class="k-doc-meta-item">
        <div class="k-doc-meta-key">{{ __('pk.translation') }}</div>
        <div class="k-doc-meta-val">
            @foreach($item->production['translation'] as $i => $productor)
                {!! $productor->nameInstance . (($loop->last ?? false) ? '.' : ',') !!}
            @endforeach
            @if(isset($item->production['translation_rev']))
                <br><span style="font-size:0.75em;color:var(--text-muted);">{{ __('pk.translation_rev') }}:</span>
                @foreach($item->production['translation_rev'] as $i => $productor)
                    {!! $productor->nameInstance . (($loop->last ?? false) ? '.' : ',') !!}
                @endforeach
            @endif
        </div>
    </div>
@endif

@if(isset($item->production['transcription']))
    <div class="k-doc-meta-item">
        <div class="k-doc-meta-key">{{ __('pk.transcription') }}</div>
        <div class="k-doc-meta-val">
            @foreach($item->production['transcription'] as $i => $productor)
                {!! $productor->nameInstance . (($loop->last ?? false) ? '.' : ',') !!}
            @endforeach
            @if(isset($item->production['transcription_rev']))
                <br><span style="font-size:0.75em;color:var(--text-muted);">{{ __('pk.transcription_rev') }}:</span>
                @foreach($item->production['transcription_rev'] as $i => $productor)
                    {!! $productor->nameInstance . (($loop->last ?? false) ? '.' : ',') !!}
                @endforeach
            @endif
        </div>
    </div>
@endif

@if(isset($item->production['edition']))
    <div class="k-doc-meta-item">
        <div class="k-doc-meta-key">{{ __('pk.edition') }}</div>
        <div class="k-doc-meta-val">
            @foreach($item->production['edition'] as $i => $productor)
                {!! $productor->nameInstance . (($loop->last ?? false) ? '.' : ',') !!}
            @endforeach
        </div>
    </div>
@endif

<div class="k-doc-meta-item">
    <div class="k-doc-meta-key">{{ __('pk.dtPublished') }}</div>
    <div class="k-doc-meta-val">{{ $item->dtPublished }}</div>
</div>
<div class="k-doc-meta-item">
    <div class="k-doc-meta-key">{{ __('pk.dtUpdated') }}</div>
    <div class="k-doc-meta-val">{{ $item->dtUpdated }}</div>
</div>

@if(count($item->links) > 0)
    <p class="k-doc-meta-section">Links</p>
    @foreach($item->links as $link)
        <div class="k-doc-meta-item">
            <a class="k-doc-meta-link" href="{{ $link->valueInstance }}" target="_blank">
                {{ $link->nameInstance }}
            </a>
        </div>
    @endforeach
@endif

<p class="k-doc-meta-section">Download / {{ __('pk.license') }}</p>

<div class="k-doc-meta-item" style="font-size:var(--text-xs);color:var(--text-muted);line-height:1.6;">
    @if($locale == 'pt')
        <p style="font-weight:600;color:var(--text-secondary);margin-bottom:4px;">Imagens</p>
        <p>CC BY-NC-ND 4.0 — cópia e compartilhamento permitidos com atribuição e sem uso comercial.</p>
        <p style="font-weight:600;color:var(--text-secondary);margin:8px 0 4px;">Transcrições e traduções</p>
        <p>CC BY 4.0 — cópia, adaptação e compartilhamento permitidos com atribuição.</p>
    @elseif($locale == 'fr')
        <p style="font-weight:600;color:var(--text-secondary);margin-bottom:4px;">Images</p>
        <p>CC BY-NC-ND 4.0 — copie et partage autorisés avec attribution, sans usage commercial.</p>
        <p style="font-weight:600;color:var(--text-secondary);margin:8px 0 4px;">Transcriptions et traductions</p>
        <p>CC BY 4.0 — copie, adaptation et partage autorisés avec attribution.</p>
    @endif
</div>

<div class="k-doc-meta-item">
    <div class="k-doc-meta-key">{{ __('pk.manuscripts') }}</div>
    <div class="k-doc-meta-val">
        @foreach($item->files as $i => $file)
            <a class="k-doc-meta-link" href="{{ $file->fullsize }}" target="_blank">{{ $i }}</a><br>
        @endforeach
    </div>
</div>

<div class="k-doc-meta-item">
    <div class="k-doc-meta-key">{{ __('pk.pdfDownload') }}</div>
    <div class="k-doc-meta-val">
        <a class="k-doc-meta-link" href="https://omeka.projetokardec.ufjf.br/items/pdf/{{ $item->idItem }}" target="_blank">
            projeto_kardec_{{ $item->idItem }}
        </a>
    </div>
</div>

<p class="k-doc-meta-section">Como citar</p>
@include('Document.citation')
