<x-layout.main>
    <x-slot:title>
        Webtool
    </x-slot:title>
    <x-slot:actions>
    </x-slot:actions>
    <x-slot:main>
        <div class="wt-container-center h-full">
            <div class="auth0-login">
                <img src="/images/fnbr_logo.png" />
                <a class="btn-login">Sign In</a>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $(".btn-login").click(function(e) {
                    e.preventDefault();
                    window.location = "/auth0Login";
                });
                $(".btn-logout").click(function(e) {
                    e.preventDefault();
                    window.location = "/auth0Logout";
                });
            });
        </script>
    </x-slot:main>
</x-layout.main>



