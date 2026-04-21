<div id="timeline2" style="width:100%;min-height:400px;"></div>
<script>
    window.addEventListener('load', function () {
        var x = {{ Js::from($timelines) }};
        var tlData = _.chain(x)
            .filter(function (d) { return d.dtStart !== ''; })
            .groupBy('group')
            .map(function (subgroup, group) {
                return {
                    group: group,
                    data: _.chain(subgroup)
                        .groupBy('subgroup')
                        .map(function (x, y) {
                            return {
                                data: _.map(x, function (z) {
                                    return {
                                        val: z.group,
                                        timeRange: [z.dtStart, z.dtEnd],
                                        info: { title: z.title, id: z.id, date: z.docDate, link: z.link }
                                    };
                                }),
                                label: y
                            };
                        })
                        .value()
                };
            })
            .value();
        if (typeof drawTimeline === 'function') {
            drawTimeline(tlData);
        }
    });
</script>
