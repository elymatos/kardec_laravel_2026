{{--
    x-search-box — Search input with submit button
    ============================================================
    Props:
      $placeholder  string  Optional. Input placeholder.
      $action       string  Required. Form action URL.
      $method       string  Optional. Form method. Default: 'GET'.
      $name         string  Optional. Input name. Default: 'q'.
      $value        string  Optional. Current search value (from request).
      $variant      string  Optional. 'dark' (default, for hero) | 'light'.
      $buttonLabel  string  Optional. Button label. Default: 'Pesquisar'.

    Usage (in hero, dark variant):
      <x-search-box
          placeholder="Pesquise por palavras, frases..."
          :action="route('pesquisar')"
          :value="request('q')"
      />

    Usage (in inner page, light variant):
      <x-search-box
          placeholder="Buscar manuscritos..."
          :action="route('manuscritos.index')"
          variant="light"
          :value="request('q')"
      />
--}}
@props([
    'placeholder' => 'Pesquise por palavras ou expressões...',
    'action'      => '',
    'method'      => 'GET',
    'name'        => 'q',
    'value'       => null,
    'variant'     => 'dark',
    'buttonLabel' => 'Pesquisar',
])

<form
    method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}"
    action="{{ $action }}"
    role="search"
>
    @if(strtoupper($method) !== 'GET')
        @csrf
    @endif

    <div class="k-search__box {{ $variant === 'light' ? 'k-search--light .k-search__box' : '' }}">
        <label for="search-input" class="sr-only">Pesquisar</label>
        <input
            type="search"
            id="search-input"
            name="{{ $name }}"
            class="k-search__input"
            placeholder="{{ $placeholder }}"
            value="{{ $value ?? old($name) }}"
            autocomplete="off"
            autocorrect="off"
            spellcheck="false"
        >
        <button type="submit" class="k-search__btn" aria-label="{{ $buttonLabel }}">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.4"/>
                <line x1="9.5" y1="9.5" x2="12.5" y2="12.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            {{ $buttonLabel }}
        </button>
    </div>

    {{-- Pass through any additional hidden fields --}}
    {{ $slot }}
</form>

{{-- Screen-reader only helper --}}
@once
<style>.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border-width:0}</style>
@endonce
