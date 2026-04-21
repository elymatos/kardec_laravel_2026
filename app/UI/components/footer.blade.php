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

      Custom description:
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
            <a href="/" class="k-footer__logo">
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
            <p class="k-footer__col-title">Acervo</p>
            <ul class="k-footer__links">
                <li><a href="/acesso/acervo">Manuscritos</a></li>
                <li><a href="/acesso/ano">Por Ano</a></li>
                <li><a href="/acesso/categoria">Por Categoria</a></li>
                <li><a href="/acesso/recente">Publicações Recentes</a></li>
                <li><a href="/timeline">Timeline</a></li>
                <li><a href="/imagens">Imagens</a></li>
                <li><a href="/biografias">Biografias</a></li>
                <li><a href="/pesquisar">Pesquisar</a></li>
            </ul>
        </div>

        {{-- Project column --}}
        <div>
            <p class="k-footer__col-title">O Projeto</p>
            <ul class="k-footer__links">
                <li><a href="/apresentacao">Apresentação</a></li>
                <li><a href="/acervos">Acervos</a></li>
                <li><a href="/equipe">Equipe</a></li>
                <li><a href="/politicaeditorial">Política Editorial</a></li>
                <li><a href="/condicoesdeuso">Condições de Uso</a></li>
                <li><a href="/bibliografia">Bibliografia</a></li>
                <li><a href="/contato">Contato</a></li>
            </ul>
        </div>

    </div>

    {{-- Partner / sponsor logos --}}
    <div class="k-footer__partners">
        <p class="k-footer__partners-label">Apoio &amp; Parceiros</p>
        <div class="k-footer__partners-logos">
            <a href="http://ufjf.br/" target="_blank" rel="noopener" aria-label="Universidade Federal de Juiz de Fora">
                <img class="k-footer__partner-logo" src="/images/partners/logo_ufjf.png" alt="UFJF">
            </a>
            <a href="https://www.ufjf.br/nupes/" target="_blank" rel="noopener" aria-label="Núcleo de Pesquisa em Espiritualidade e Saúde">
                <img class="k-footer__partner-logo" src="https://projetokardec.ufjf.br/images/logo_nupes.png" alt="Nupes">
            </a>
            <a href="https://feal.com.br/" target="_blank" rel="noopener" aria-label="Fundação Espírita André Luiz / CDOR">
                <img class="k-footer__partner-logo" src="https://projetokardec.ufjf.br/images/logo_feal_cdor.png" alt="FEAL · CDOR">
            </a>
            <a href="https://www.allankardec.online/" target="_blank" rel="noopener" aria-label="Museu Allan Kardec Online">
                <img class="k-footer__partner-logo" src="/images/partners/logo_akol_new.png" alt="AllanKardec.online">
            </a>
        </div>
    </div>

    <div class="k-footer__bottom">
        <span class="k-footer__copy">{{ $copyright }}</span>
        <span class="k-footer__inst">{{ $institution }}</span>
    </div>
</footer>
