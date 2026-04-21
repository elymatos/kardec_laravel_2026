<x-layout.index title="Contato">

    <x-slot:head>
        <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                emailjs.init({ publicKey: "ldyx4meviSEhcUjiP" });
                emailjs.send('service_uoqpabp', 'template_contact_form', {
                    'from_name':    "{{ addslashes($name) }}",
                    'from_email':   "{{ addslashes($email) }}",
                    'from_subject': "{{ addslashes($subject) }}",
                    'message':      "{{ addslashes($text) }}",
                });
            });
        </script>
    </x-slot:head>

    <x-page-header
        eyebrow="O Projeto"
        title="Contato"
        description="Envie seus comentários, sugestões ou dúvidas sobre o acervo."
    />

    <x-section variant="light">
        <div style="max-width:560px;">
            <p style="color:var(--text-secondary);margin-bottom:var(--space-4);">
                Obrigado, <strong>{{ $name }}</strong>! Sua mensagem foi enviada com sucesso.
            </p>
            <p style="color:var(--text-muted);font-size:var(--text-sm);margin-bottom:var(--space-8);">
                Retornaremos em breve para <em>{{ $email }}</em>.
            </p>
            <a href="/contato" class="k-nav__cta" style="text-decoration:none;">
                Enviar outra mensagem
            </a>
        </div>
    </x-section>

</x-layout.index>
