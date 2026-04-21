@php
    $challenge = uniqid(rand());
    session(['challenge', $challenge]);
@endphp
<x-layout.main>
    <x-slot:actions>
            Webtool
    </x-slot:actions>
    <x-slot:title>
    </x-slot:title>
    <x-slot:main>
        <div class="wt-container-center h-full">
            <div id="formLoginDiv">
                @fragment('form')
                    <x-form
                        id="formLogin"
                        title="Login"
                        center="true"
                        hx-post="/login"
                        hx-target="#formLoginDiv"
                    >
                        <x-slot:fields>
                            <div style="text-align: center">
                                <img src="/images/fnbr_logo.png" />
                            </div>
                            <x-text-field
                                id="login"
                                label="Login"
                                value=""
                            ></x-text-field>
                            <x-password-field
                                id="password"
                                label="Password"
                            ></x-password-field>
                        </x-slot:fields>

                        <x-slot:buttons>
                            <x-submit
                                label="Login"
                            ></x-submit>
                        </x-slot:buttons>
                    </x-form>
                    <script>
                        $(function() {
                            $("#formLogin").on("htmx:beforeRequest", event => {
                                let p = event.detail.requestConfig.parameters.password;
                                event.detail.requestConfig.parameters.password = md5(p);
                            });
                        });
                    </script>
                @endfragment
            </div>
        </div>
    </x-slot:main>
</x-layout.main>
