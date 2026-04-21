<div
    x-data="{ lightbox: null }"
    @keydown.escape.window="lightbox = null"
>
    <figure class="k-ms-gallery">
        @foreach($item->files as $id => $file)
            <button
                class="k-ms-gallery__thumb"
                @click="lightbox = { src: '{{ $file->fullsize }}', caption: '{{ addslashes($id) }}' }"
                type="button"
                aria-label="{{ $id }}"
            >
                <img
                    src="{{ $file->thumbnail }}"
                    alt="{{ $id }}"
                    loading="lazy"
                >
                <figcaption class="k-ms-gallery__caption">{{ $id }}</figcaption>
            </button>
        @endforeach
    </figure>

    {{-- Lightbox --}}
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
            <img class="k-lightbox__img" :src="lightbox?.src" :alt="lightbox?.caption">
            <div class="k-lightbox__caption">
                <p x-text="lightbox?.caption"></p>
            </div>
        </div>
    </div>
</div>
