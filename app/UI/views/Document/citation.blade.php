<div id="doc-citation" class="k-doc-meta-item">
    @include('Document.citationDetail', ['style' => 'associacao-brasileira-de-normas-tecnicas.csl'])
</div>

<div class="k-doc-meta-item" style="margin-top:var(--space-3);">
    <label class="k-label" for="doc-citation-style" style="font-size:var(--text-xs);">Formato de citação</label>
    <select
        class="k-select"
        id="doc-citation-style"
        name="style"
        hx-get="/item/{{ $item->idItem }}/citation"
        hx-trigger="change"
        hx-target="#doc-citation"
        style="max-width:240px;"
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
