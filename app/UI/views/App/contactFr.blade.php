<x-layout.main>
    <x-slot:title>
        Contact
    </x-slot:title>
    <x-slot:actions>
    </x-slot:actions>
    <x-slot:main>
        <div>
            <x-form
                id="frmContact"
                :center="false"
            >
                <x-slot:fields>
                    <x-hidden-field id="gresponse" value=""></x-hidden-field>
                    <div class="field">
                        <label>Merci de nous faire part de vos commentaires et suggestions.</label>
                    </div>
                    <div class="field">
                        <x-text-field
                            id="name"
                            label='Nom'
                            value=""
                        ></x-text-field>
                    </div>
                    <div class="field">
                        <x-text-field
                            id="email"
                            label='email'
                            value=""
                        ></x-text-field>
                    </div>
                    <div class="field">
                        <x-text-field
                            id="subject"
                            label='Sujet'
                            value=""
                        ></x-text-field>
                    </div>
                    <div class="field">
                        <x-multiline-field
                            id="text"
                            label='Message'
                            value=""
                        ></x-multiline-field>
                    </div>
                    <div class="g-recaptcha" data-sitekey="6Lcly8YZAAAAAONw54D4Q_AQDWTOHaQpLQJ7dMs3" data-callback="recaptchaCallBack"></div>
                </x-slot:fields>
                <x-slot:buttons>
                    <x-button
                        id="btnPost"
                        type="button"
                        label="Envoyer"
                        color="primary"
                        onclick="doSubmit()"
                    ></x-button>
                </x-slot:buttons>
            </x-form>
        </div>
        <script>

            emailjs.init({
                publicKey: "ldyx4meviSEhcUjiP",
            });

            recaptchaCallBack = (gresponse) => {
                document.getElementById("gresponse").value = gresponse;
            }

            doSubmit = () => {
                console.log("submitting");
                try {
                    emailjs.send('service_uoqpabp', 'template_contact_form', {
                            'from_name': document.getElementById("name").value,
                            'from_email': document.getElementById("email").value,
                            'from_subject': document.getElementById("subject").value,
                            'message': document.getElementById("text").value,
                            'g-recaptcha-response': document.getElementById("gresponse").value
                        },
                        'ldyx4meviSEhcUjiP')
                    manager.notify("success", "Message envoyé.");
                } catch (error) {
                    manager.notify("error", "Erreur lors de l'envoi du message.");
                    console.log({error});
                }
            }


        </script>
    </x-slot:main>
</x-layout.main>

