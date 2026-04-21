<x-layout.main>
    <x-slot:title>
        Rechercher
    </x-slot:title>
    <x-slot:actions>
    </x-slot:actions>
    <x-slot:main>
        <div>
            <x-form
                id="frmSearch"
                :center="false"
                hx-post="/pesquisar"
                hx-target="#results"
            >
                <x-slot:fields>
                    <x-text-field
                        id="search"
                        label='Rechercher des mots ou des expressions entre guillemets (ex. "Livre des Esprits")'
                        value=""
                    ></x-text-field>
                    <div class="formgrid grid" style="overflow:initial">
                        <x-number-field
                            id="idItem"
                            label="Identifiant (ex. 108)"
                            value=""
                            class="w-15 col"
                        ></x-number-field>
                        <x-combobox.collection
                            id="collectionCode"
                            label="Collection"
                            value=""
                            class="col"
                        ></x-combobox.collection>
                        <x-combobox.year
                            id="year"
                            label="An"
                            value=""
                            class="col"
                        ></x-combobox.year>
                        <x-combobox.tag
                            id="idTag"
                            label="Catégorie"
                            value=""
                            class="col"
                        ></x-combobox.tag>
                    </div>
                </x-slot:fields>
                <x-slot:buttons>
                    <x-button
                        id="btnPost"
                        label="Rechercher"
                        color="primary"
                    ></x-button>
                    <x-button
                        id="btnReset"
                        type="reset"
                        label="Nouveau rescherche"
                        color="secondary"
                    ></x-button>
                </x-slot:buttons>
            </x-form>
        </div>
        <div
            class="flex-grow-1 flex flex-column mt-2 overflow-y-auto"
        >
            <div
                class="relative top-0 left-0"
                id="results"
            >
            </div>
        </div>
    </x-slot:main>
</x-layout.main>
