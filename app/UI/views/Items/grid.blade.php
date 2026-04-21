<div
    class="h-full"
>
    <div class="relative h-full overflow-auto">
        <div id="itemTreeWrapper" class="ui striped small compact table absolute top-0 left-0 bottom-0 right-0">
            @fragment('search')
                <ul id="itemTree">
                </ul>
                <script>
                    $(function() {
                        $("#itemTree").datagrid({
                            url:"/items/data",
                            method:"get",
                            fit: true,
                            singleSelect:true,
                            showHeader: true,
                            rownumbers: false,
                            idField: "id",
                            treeField: "text",
                            showFooter: false,
                            border: false,
                            columns: [[
                                {
                                    field: "id",
                                    width: "5%",
                                    title:"idItem",
                                    sortable: true,
                                    hstyler: () => {
                                        return "font-weight:bold;";
                                    },
                                },
                                {
                                    field: "name",
                                    width: "70%",
                                    title:"Name",
                                    sortable: true,
                                    hstyler: () => {
                                        return "font-weight:bold;";
                                    },
                                },
                                {
                                    field: "docDate",
                                    width: "20%",
                                    title: "Doc Date",
                                    sortable: true,
                                    hstyler: () => {
                                        return "font-weight:bold;";
                                    },
                                },
                                {
                                    field: "public",
                                    width: "5%",
                                    title: "pub",
                                    sortable: true,
                                    hstyler: () => {
                                        return "font-weight:bold;";
                                    },
                                },
                            ]],
                            onClickRow: (index, row) => {
                                if (row.type === "item") {
                                    htmx.ajax("GET", `/items/${row.id}/edit`, "#editArea");
                                }
                            }
                        });
                    });
                </script>
            @endfragment
        </div>
    </div>
</div>
