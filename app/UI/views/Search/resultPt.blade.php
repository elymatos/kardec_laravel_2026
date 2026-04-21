{{-- HTMX fragment — no layout wrapper --}}
@if(!empty($results))
    <ul class="k-access-list k-access-list--flat">
        @foreach($results as $idItem => $item)
            <li>
                <a class="k-access-item" href="/item-pt?id={{ $item->idItem }}" target="_blank">
                    <span class="k-access-item__id">[{{ $item->idItem }}]</span>
                    <span class="k-access-item__title">{{ $item->title }}</span>
                    <span class="k-access-item__date">{{ $item->docDate }}</span>
                </a>
                @if(!empty($item->sentences))
                    @foreach($item->sentences as $sentence)
                        <div style="padding:0.25rem 1rem 0.5rem 2.5rem;font-size:0.85rem;color:var(--text-muted);border-left:2px solid rgba(184,137,42,0.2);margin-left:1rem;">
                            {!! $sentence->text !!}
                        </div>
                    @endforeach
                @endif
            </li>
        @endforeach
    </ul>
@else
    <p style="font-family:var(--font-mono);font-size:var(--text-sm);color:var(--text-muted);letter-spacing:var(--tracking-wider);text-transform:uppercase;">
        Nenhum resultado encontrado.
    </p>
@endif
