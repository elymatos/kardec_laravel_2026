<x-layout.index title="Imagens">

    <x-page-header
        eyebrow="Acervo"
        title="Imagens"
        description="Fotografias, litografias e documentos iconográficos do acervo."
    />

    <x-section variant="light">
        @if(empty($images))
            <p style="color:var(--text-muted);font-family:var(--font-mono);font-size:var(--text-sm);">
                Nenhuma imagem disponível no momento.
            </p>
        @else
            <div
                x-data="{ lightbox: null }"
                @keydown.escape.window="lightbox = null"
            >
                {{-- Image grid --}}
                <div class="k-image-grid">
                    @foreach($images as $image)
                        @php
                            $hasFiles = is_object($image->files) && !empty($image->files->original);
                            $thumb    = $hasFiles ? $image->files->thumbnail : null;
                            $original = $hasFiles ? $image->files->original  : null;
                        @endphp

                        <button
                            class="k-image-thumb"
                            @if($hasFiles)
                                @click="lightbox = { src: '{{ $original }}', title: '{{ addslashes($image->title) }}', collection: '{{ addslashes($image->collection) }}' }"
                            @endif
                            aria-label="{{ $image->title }}"
                            @if(!$hasFiles) disabled style="opacity:.45;cursor:default;" @endif
                        >
                            @if($thumb)
                                <img src="{{ $thumb }}" alt="{{ $image->title }}" loading="lazy">
                            @else
                                <div style="width:100%;aspect-ratio:3/4;background:var(--color-ink-800);display:flex;align-items:center;justify-content:center;">
                                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                                        <rect x="2" y="6" width="28" height="20" rx="2" stroke="rgba(184,137,42,0.3)" stroke-width="1.5"/>
                                        <circle cx="10" cy="12" r="2.5" stroke="rgba(184,137,42,0.3)" stroke-width="1.5"/>
                                        <path d="M2 22l8-6 6 5 4-3 10 7" stroke="rgba(184,137,42,0.3)" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                            @endif

                            <div class="k-image-thumb__caption">
                                <p class="k-image-thumb__title">{{ $image->title }}</p>
                                <p class="k-image-thumb__collection">{{ $image->collection }}</p>
                            </div>
                        </button>
                    @endforeach
                </div>

                {{-- Lightbox modal --}}
                <div
                    class="k-lightbox"
                    x-show="lightbox"
                    x-cloak
                    @click.self="lightbox = null"
                    role="dialog"
                    aria-modal="true"
                >
                    <div class="k-lightbox__inner">
                        <button class="k-lightbox__close" @click="lightbox = null" aria-label="Fechar">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <line x1="2" y1="2" x2="18" y2="18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <line x1="18" y1="2" x2="2" y2="18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <img class="k-lightbox__img" :src="lightbox?.src" :alt="lightbox?.title">
                        <div class="k-lightbox__caption">
                            <p x-text="lightbox?.title"></p>
                            <p class="k-lightbox__collection" x-text="lightbox?.collection"></p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-section>

</x-layout.index>
