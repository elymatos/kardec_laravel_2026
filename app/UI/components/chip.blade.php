{{--
    x-chip — Toggleable filter pill
    ============================================================
    Props:
      $label    string  Required. Display text.
      $value    string  Optional. Filter value (used in Alpine/form context).
      $variant  string  Optional. 'dark' | 'light'. Default: 'dark'.
      $active   bool    Optional. Initially active. Default: false.
      $dotColor string  Optional. Colored dot hex. Omit to hide dot.

    Alpine.js usage (client-side filtering):
      <div x-data="{ active: 'all' }">
          <x-chip label="Todos"         value="all"        :active="true" />
          <x-chip label="Cartas"        value="carta"      />
          <x-chip label="Dissertações"  value="dissertacao"/>
      </div>

    Form usage (server-side filtering via GET):
      <x-chip
          label="Carta"
          value="carta"
          :active="request('categoria') === 'carta'"
          href="{{ request()->fullUrlWithQuery(['categoria' => 'carta']) }}"
      />
--}}
@props([
    'label'    => '',
    'value'    => '',
    'variant'  => 'dark',
    'active'   => false,
    'dotColor' => null,
    'href'     => null,
])

@if($href)
    {{-- Link-based chip (server-side filter) --}}
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => "k-chip k-chip--{$variant}" . ($active ? ' is-active' : '')]) }}
    >
        @if($dotColor)
            <span class="k-chip__dot" style="background: {{ $dotColor }}" aria-hidden="true"></span>
        @endif
        {{ $label }}
    </a>
@else
    {{-- Button chip (Alpine.js client-side filter) --}}
    <button
        type="button"
        data-value="{{ $value }}"
        {{ $attributes->merge(['class' => "k-chip k-chip--{$variant}" . ($active ? ' is-active' : '')]) }}
    >
        @if($dotColor)
            <span class="k-chip__dot" style="background: {{ $dotColor }}" aria-hidden="true"></span>
        @endif
        {{ $label }}
    </button>
@endif
