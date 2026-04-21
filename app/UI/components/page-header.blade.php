{{--
    x-page-header — Inner page header (non-hero)
    ============================================================
    Use for all pages except the landing page (which uses x-hero).

    Props:
      $eyebrow     string  Optional. Small category label.
      $title       string  Required. Page title.
      $description string  Optional. Short description / subtitle.

    Slots:
      $actions     Optional. Buttons / controls to the right of the title.

    Usage:
      <x-page-header
          eyebrow="Acervo"
          title="Manuscritos"
          description="Documentos originais de Allan Kardec disponíveis para consulta."
      >
          <x-slot:actions>
              <x-search-box
                  placeholder="Filtrar manuscritos..."
                  :action="route('manuscritos.index')"
                  variant="light"
                  :value="request('q')"
              />
          </x-slot:actions>
      </x-page-header>
--}}
@props([
    'eyebrow'     => null,
    'title'       => '',
    'description' => null,
])

<header class="k-page-header">
    <div class="k-page-header__inner">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:2rem;flex-wrap:wrap;">
            <div>
                @if($eyebrow)
                    <p class="k-page-header__eyebrow">{{ $eyebrow }}</p>
                @endif
                <h1 class="k-page-header__title">{{ $title }}</h1>
                @if($description)
                    <p class="k-page-header__desc">{{ $description }}</p>
                @endif
            </div>

            @if(isset($actions))
                <div>{{ $actions }}</div>
            @endif
        </div>
    </div>
</header>
