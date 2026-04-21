<x-layout.index title="Contato">

    <x-page-header
        eyebrow="O Projeto"
        title="Contato"
        description="Envie seus comentários, sugestões ou dúvidas sobre o acervo."
    />

    <x-section variant="light">
        <div style="max-width:560px;">
            <p style="color:var(--text-secondary);margin-bottom:var(--space-8);">
                Obrigado por nos enviar seus comentários e sugestões. Preencha o formulário abaixo e retornaremos em breve.
            </p>

            <form id="frmContact" method="POST" action="/contato" style="display:flex;flex-direction:column;gap:var(--space-5);">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="k-label" for="name">Nome</label>
                    <input class="k-input" type="text" id="name" name="name" required>
                </div>
                <div>
                    <label class="k-label" for="email">E-mail</label>
                    <input class="k-input" type="email" id="email" name="email" required>
                </div>
                <div>
                    <label class="k-label" for="subject">Assunto</label>
                    <input class="k-input" type="text" id="subject" name="subject" required>
                </div>
                <div>
                    <label class="k-label" for="text">Mensagem</label>
                    <textarea class="k-input" id="text" name="text" rows="6" required style="resize:vertical;"></textarea>
                </div>

                <div class="g-recaptcha" data-sitekey="6Lcly8YZAAAAAONw54D4Q_AQDWTOHaQpLQJ7dMs3"></div>

                <div>
                    <button type="submit" class="k-nav__cta" style="border:none;cursor:pointer;">
                        Enviar mensagem
                    </button>
                </div>
            </form>
        </div>
    </x-section>

    <x-slot:scripts>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    </x-slot:scripts>

</x-layout.index>
