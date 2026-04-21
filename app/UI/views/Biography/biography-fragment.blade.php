@php $hasBio = is_object($itemBio); @endphp

@if(!$hasBio)
    <p style="color:var(--text-muted);font-family:var(--font-mono);font-size:var(--text-sm);">
        Conteúdo não disponível no momento.
    </p>
@else
    <h2 style="font-family:var(--font-display);font-size:var(--text-2xl);color:var(--text-primary);margin-bottom:var(--space-6);">
        {{ $itemBio->title }}
    </h2>

    <div class="k-prose" style="margin-bottom:var(--space-8);">
        {!! $itemBio->text !!}
    </div>

    <div class="k-bio-citation">
        <p class="k-bio-citation__label">Como citar:</p>
        <div id="citation" class="k-bio-citation__content">
            @include('Biography.citationDetail', ['style' => 'associacao-brasileira-de-normas-tecnicas.csl'])
        </div>
        <div class="k-bio-citation__selector">
            <label class="k-label" for="citation-style">Formato de citação</label>
            <select
                class="k-select"
                id="citation-style"
                name="style"
                hx-get="/biografias/item/{{ $idItem }}/citation"
                hx-trigger="change"
                hx-target="#citation"
                style="max-width:260px;"
            >
                <option value="associacao-brasileira-de-normas-tecnicas.csl">ABNT</option>
                <option value="apa.csl">APA</option>
                <option value="chicago-author-date.csl">Chicago</option>
                <option value="harvard-cite-them-right.csl">Harvard</option>
                <option value="ieee.csl">IEEE</option>
                <option value="modern-language-association.csl">MLA</option>
                <option value="vancouver.csl">Vancouver</option>
            </select>
        </div>
    </div>
@endif
