{{--
    x-footer — Site footer
    ============================================================
    Props (all optional, defaults match Projeto Allan Kardec):
      $title        string  Site name.
      $subtitle     string  Tagline (italic, gold).
      $letter       string  Logo mark letter.
      $description  string  Short paragraph about the project.
      $institution  string  Institution credit line.
      $copyright    string  Copyright string.

    Usage:
      <x-footer />

      {{-- Custom description --}}
      <x-footer description="Seu texto aqui." />
--}}
@props([
    'title'       => 'Projeto Allan Kardec',
    'subtitle'    => 'Acervo Digital UFJF',
    'letter'      => 'K',
    'description' => 'Plataforma digital de acesso a manuscritos, documentos e transcrições originais de Allan Kardec, desenvolvida pela Universidade Federal de Juiz de Fora com apoio de instituições parceiras.',
    'institution' => 'Universidade Federal de Juiz de Fora — FAPEMIG APQ-04113-23',
    'copyright'   => '© ' . date('Y') . ' Projeto Allan Kardec · UFJF · Todos os direitos reservados',
])

<footer class="k-footer">
    <div class="k-footer__grid">

        {{-- Brand column --}}
        <div>
            <a href="{{ route('home') }}" class="k-footer__logo">
                <span class="k-footer__logo-mark">{{ $letter }}</span>
                <span class="k-footer__logo-text">
                    {{ $title }}
                    <small>{{ $subtitle }}</small>
                </span>
            </a>
            <p class="k-footer__desc">{{ $description }}</p>
        </div>

        {{-- Navigation column --}}
        <div>
            <p class="k-footer__col-title">Navegar</p>
            <ul class="k-footer__links">
                <li><a href="{{ route('manuscritos.index') }}">Manuscritos</a></li>
                <li><a href="{{ route('transcricoes.index') }}">Transcrições</a></li>
                <li><a href="{{ route('timeline') }}">Timeline</a></li>
                <li><a href="{{ route('sobre') }}">O Projeto</a></li>
                <li><a href="{{ route('biografias.index') }}">Biografias</a></li>
            </ul>
        </div>

        {{-- Project column --}}
        <div>
            <p class="k-footer__col-title">Projeto</p>
            <ul class="k-footer__links">
                <li><a href="{{ route('apresentacao') }}">Apresentação</a></li>
                <li><a href="{{ route('acervos') }}">Acervos</a></li>
                <li><a href="{{ route('politica-editorial') }}">Política Editorial</a></li>
                <li><a href="{{ route('equipe') }}">Equipe</a></li>
                <li><a href="{{ route('contato') }}">Contato</a></li>
                <li><a href="{{ route('condicoes-de-uso') }}">Condições de Uso</a></li>
            </ul>
        </div>

    </div>

    <div class="k-footer__bottom">
        <span class="k-footer__copy">{{ $copyright }}</span>
        <span class="k-footer__inst">{{ $institution }}</span>
    </div>
</footer>
