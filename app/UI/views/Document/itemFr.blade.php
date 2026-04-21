@php $hasItem = is_object($item); @endphp

<x-layout.index :title="$hasItem ? ($item->title . ' [' . $item->docDate . ']') : 'Manuscrit'">

    <x-page-header
        eyebrow="Fonds"
        :title="$hasItem ? $item->title : 'Manuscrit'"
    >
        @if($hasItem)
            <x-slot:actions>
                <div style="display:flex;gap:var(--space-2);flex-wrap:wrap;align-items:center;">
                    <span class="k-badge">#{{ $item->idItem }}</span>
                    @foreach($item->tags as $tag)
                        <span class="k-badge k-badge--outline">{{ $tag->ptName }}</span>
                    @endforeach
                </div>
            </x-slot:actions>
        @endif
    </x-page-header>

    <x-section variant="dark">
        @if(!$hasItem)
            <p style="color:var(--text-muted);font-family:var(--font-mono);font-size:var(--text-sm);">
                Document non disponible pour le moment.
            </p>
        @else
            <div class="k-doc-layout" x-data="{ tab: 'manuscripts' }">

                {{-- Left: tabbed content --}}
                <div class="k-doc-main">

                    <div class="k-doc-tabs" role="tablist">
                        <button
                            class="k-doc-tab"
                            :class="{ 'is-active': tab === 'manuscripts' }"
                            @click="tab = 'manuscripts'"
                            role="tab"
                        >Manuscrits</button>
                        <button
                            class="k-doc-tab"
                            :class="{ 'is-active': tab === 'transcription' }"
                            @click="tab = 'transcription'"
                            role="tab"
                        >Transcription</button>
                        <button
                            class="k-doc-tab"
                            :class="{ 'is-active': tab === 'closeDates' }"
                            @click="tab = 'closeDates'"
                            role="tab"
                        >Dates proches</button>
                    </div>

                    <div class="k-doc-panel" x-show="tab === 'manuscripts'">
                        @include('Document.manuscripts')
                    </div>

                    <div class="k-doc-panel k-prose" x-show="tab === 'transcription'">
                        @if($item->transcription)
                            {!! $item->transcription !!}
                        @else
                            <p><em>Transcription non disponible.</em></p>
                        @endif
                    </div>

                    <div class="k-doc-panel" x-show="tab === 'closeDates'">
                        @include('Document.closeDatesPt')
                    </div>

                </div>

                {{-- Right: metadata sidebar --}}
                <div class="k-doc-sidebar">
                    @include('Document.metadataPt')
                </div>

            </div>
        @endif
    </x-section>

</x-layout.index>
