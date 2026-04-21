<x-layout.index title="Timeline">

    <x-page-header
        eyebrow="Explorar"
        title="Linha do Tempo"
        description="Clique e arraste para fazer zoom. Clique em um segmento para abrir o documento."
    />

    <x-section variant="dark">
        <div class="k-timeline-page">

            {{-- Category filter sidebar --}}
            <div class="k-timeline-page__sidebar">
                <form
                    hx-post="/timeline/update"
                    hx-target="#timeline2-container"
                    hx-trigger="change"
                    x-data="timelineCategories()"
                >
                    <p class="k-timeline-page__filter-label">Filtrar categorias</p>

                    @foreach($categories as $group => $subgroups)
                        <div class="k-timeline-page__group">
                            <label class="k-timeline-page__master">
                                <input
                                    type="checkbox"
                                    class="k-checkbox"
                                    x-model="groups['{{ $group }}']"
                                    @change="syncChildren('{{ $group }}')"
                                >
                                <span>{{ $group }}</span>
                            </label>

                            @foreach($subgroups as $subgroup)
                                <label class="k-timeline-page__child">
                                    <input
                                        type="checkbox"
                                        class="k-checkbox"
                                        name="subgroup[{{ $subgroup->subgroup }}]"
                                        x-model="children['{{ $subgroup->subgroup }}']"
                                        @change="syncParent('{{ $group }}')"
                                        checked
                                    >
                                    <span>{{ $subgroup->subgroup }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endforeach

                </form>
            </div>

            {{-- Timeline visualization --}}
            <div id="timeline2-container" class="k-timeline-page__chart">
                @include('Timeline.timeline')
            </div>

        </div>
    </x-section>

    <x-slot:head>
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/underscore@1.13.6/underscore-min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/d3@7/dist/d3.min.js"></script>
        <script src="https://unpkg.com/timelines-chart@2"></script>
    </x-slot:head>

    <x-slot:scripts>
        <script>
            function timelineCategories() {
                return {
                    groups: {},
                    children: {},
                    syncChildren(group) {
                        const checked = this.groups[group];
                        document.querySelectorAll(`[data-group="${group}"]`).forEach(el => {
                            el.checked = checked;
                            this.children[el.value] = checked;
                        });
                    },
                    syncParent(group) {
                        const siblings = document.querySelectorAll(`[data-group="${group}"]`);
                        const allChecked = [...siblings].every(el => el.checked);
                        const noneChecked = [...siblings].every(el => !el.checked);
                        this.groups[group] = allChecked;
                    }
                }
            }

            const drawTimeline = (data) => {
                const timelineDiv = document.getElementById("timeline2");
                if (!timelineDiv || typeof TimelinesChart === 'undefined') return;

                TimelinesChart({
                    messages: {
                        zoom: {
                            reset: 'Remover Zoom',
                            title: 'Clique e arraste para Zoom'
                        }
                    }
                })(timelineDiv)
                    .data(data)
                    .overviewDomain([new Date('01/01/1800'), new Date('01/01/1880')])
                    .width(timelineDiv.parentElement.clientWidth - 8)
                    .maxLineHeight(24)
                    .zQualitative(true)
                    .useUtc(false)
                    .timeFormat("%d/%m/%Y")
                    .segmentTooltipContent(toolTip)
                    .onSegmentClick(onSegmentClick)
                    .xTickFormat(multiFormat)
                    .leftMargin(128)
                    .topMargin(36);
            };

            var locale = d3.timeFormatLocale({
                "dateTime": "%A, %e de %B de %Y. %X",
                "date": "%d/%m/%Y",
                "time": "%H:%M:%S",
                "periods": ["AM", "PM"],
                "days": ["Domingo","Segunda","Terça","Quarta","Quinta","Sexta","Sábado"],
                "shortDays": ["Dom","Seg","Ter","Qua","Qui","Sex","Sáb"],
                "months": ["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],
                "shortMonths": ["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"]
            });

            var formatDay   = locale.format("%d/%m"),
                formatWeek  = locale.format("%d/%m"),
                formatMonth = locale.format("%b/%Y"),
                formatYear  = locale.format("%Y");

            function multiFormat(date) {
                if (d3.timeMonth(date) < date) {
                    return d3.timeWeek(date) < date ? formatDay(date) : formatWeek(date);
                }
                return d3.timeYear(date) < date ? formatMonth(date) : formatYear(date);
            }

            function toolTip(d) {
                if (d.data.info) {
                    return `<div style="width:200px;"><span>${d.data.info.title}</span><br><span>${d.data.info.date}</span></div>`;
                }
                return '';
            }

            function onSegmentClick(segment) {
                if (segment.data.info.link !== '') {
                    window.open(segment.data.info.link, '_blank');
                }
            }
        </script>
    </x-slot:scripts>

</x-layout.index>
