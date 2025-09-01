@extends('layouts.backend')
@section('content')

<section class="content-header">
    <h1>
        Working Day Report
        <small>Report Panel</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i> Home</a></li>
        <li><a href="#"><i class="fa fa-calendar"></i> Working Day</a></li>
        <li><a href="#">Report</a></li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <section class="col-md-12 col-lg-12 connectedSortable">
            <div class="box box-danger box-solid">
                <div class="box-header with-border">
                    <h3 id="bartitle" class="box-title">Working Day Report</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-12">
                            <div id="filterForm"></div>
                            <div style="margin-top: 20px;">
                                <div class="inner">
                                    <div id="loadReport"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div id="reportGrid"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</section>

<style>
    .inner {
        display: inline-block;
    }
    #filterForm .dx-field {
        margin-bottom: 15px;
    }
    #reportGrid {
        margin-top: 20px;
    }
</style>

<script>
$(document).ready(function() {
    const now = new Date();

    $("#filterForm").dxForm({
        formData: {
            period: now,
            area: [],
            virtual: "No"
        },
        labelLocation: "top",
        colCount: 2,
        items: [
            {
                itemType: "group",
                colCount: 3,
                items: [
                    {
                        dataField: "period",
                        label: { text: "Period" },
                        editorType: "dxDateBox",
                        isRequired: true,
                        editorOptions: {
                            pickerType: 'calendar',
                            displayFormat: 'monthAndYear',
                            openOnFieldClick: true,
                            calendarOptions: {
                                maxZoomLevel: 'year',
                                minZoomLevel: 'century',
                            },
                            width: "100%",
                            type: "date"
                        }
                    },
                    {
                        dataField: "area",
                        label: { text: "Area" },
                        editorType: "dxDropDownBox",
                        editorOptions: {
                            placeholder: "Select Area(s)",
                            dataSource: [
                                @foreach($districtAreas as $area)
                                { text: "{{ $area['text'] }}", value: "{{ $area['value'] }}" },
                                @endforeach
                            ],
                            displayExpr: "text",
                            valueExpr: "value",
                            showClearButton: true,
                            multiline: false,
                            width: "100%",
                            contentTemplate: function(e) {
                                const $list = $("<div>").dxList({
                                    dataSource: e.component.option("dataSource"),
                                    displayExpr: "text",
                                    valueExpr: "value",
                                    selectionMode: "multiple",
                                    showSelectionControls: true,
                                    onSelectionChanged: function(args) {
                                        const selectedItems = args.component.option("selectedItems");
                                        const values = selectedItems.map(function(item) {
                                            return item.value;
                                        });
                                        e.component.option("value", values);
                                        e.component.close();
                                    }
                                });
                                return $list;
                            }
                        },
                        isRequired: false
                    },
                    {
                        dataField: "virtual",
                        label: { text: "Virtual" },
                        editorType: "dxSelectBox",
                        editorOptions: {
                            items: ["Yes", "No"],
                            width: "100%",
                            placeholder: "Select Virtual Status"
                        },
                        isRequired: true
                    }
                ]
            }
        ]
    });

    $("#loadReport").dxButton({
        text: "Load Report",
        type: "default",
        icon: "fa fa-refresh",
        useSubmitBehavior: true,
        onClick: function(e) {
            loadReportData();
        }
    });

    $("#reportGrid").dxDataGrid({
        dataSource: [],
        allowColumnReordering: true,
        allowColumnResizing: true,
        showRowLines: true,
        columnAutoWidth: true,
        selection: {
            mode: "single"
        },
        filterRow: {
            visible: true
        },
        hoverStateEnabled: true,
        groupPanel: {
            visible: true
        },
        export: {
            enabled: true,
            fileName: "Working_Day_Report",
            allowExportSelectedData: false
        },
        onExporting: function(e) {
            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet('Working Day Report');

            DevExpress.excelExporter.exportDataGrid({
                component: e.component,
                worksheet: worksheet,
                autoFilterEnabled: true
            }).then(function() {
                workbook.xlsx.writeBuffer().then(function(buffer) {
                    saveAs(new Blob([buffer], { type: 'application/octet-stream' }), "Working_Day_Report.xlsx");
                });
            });
            e.cancel = true;
        },
        showBorders: true,
        paging: {
            enabled: true,
            pageIndex: 0,
            pageSize: 20
        },
        pager: {
            showPageSizeSelector: true,
            allowedPageSizes: [10, 25, 50, 100]
        },
        remoteOperations: {
            paging: true,
            sorting: true,
            filtering: true
        },
        columns: [
            {
                dataField: "area",
                caption: "Area",
                allowEditing: false,
                fixed: true
            },
            {
                dataField: "employee_name",
                caption: "Employee Name",
                allowEditing: false,
                fixed: true
            },
            {
                dataField: "employee_id",
                caption: "NIK Essity",
                visible: false,
                allowEditing: false,
                fixed: true
            },
            {
                dataField: "final_working_days",
                caption: "Final Working Days",
                dataType: "number",
                alignment: "left",
                allowEditing: false,
                fixed: true
            },
            {
                dataField: "standard_working_days",
                caption: "Standard Working Days",
                dataType: "number",
                alignment: "left",
                allowEditing: false,
                fixed: true
            },
            {
                dataField: "working_days_with_adjustment",
                caption: "Working Days with Adjustment",
                dataType: "number",
                alignment: "left",
                allowEditing: false,
                fixed: true
            },
            {
                dataField: "asm_adjustment",
                caption: "Adjustment from ASM",
                dataType: "number",
                alignment: "left",
                allowEditing: false,
                fixed: true
            },
            {
                dataField: "note",
                caption: "Note Adjustment",
                allowEditing: false,
                fixed: true
            },
            {
                dataField: "other_days",
                caption: "BR/Training/Event",
                dataType: "number",
                alignment: "left",
                allowEditing: false,
                fixed: true
            },
            {
                dataField: "final_total_visits",
                caption: "Grand Total",
                dataType: "number",
                alignment: "left",
                allowEditing: false,
                fixed: true
            }
        ],
        summary: {
            groupItems: [
                {
                    column: "final_working_days",
                    summaryType: "sum",
                    valueFormat: "fixedPoint",
                    alignByColumn: true,
                    displayFormat: "{0}"
                },
                {
                    column: "final_total_visits",
                    summaryType: "sum",
                    valueFormat: "fixedPoint",
                    alignByColumn: true,
                    displayFormat: "{0}"
                }
            ],
            totalItems: [
                {
                    column: "employee_name",
                    displayFormat: "Total Records: {0}"
                }
            ]
        }
    });

    async function loadReportData() {
        const formInstance = $("#filterForm").dxForm("instance");
        if (!formInstance.validate().isValid) {
            DevExpress.ui.notify({
                message: "Please fill in all required fields.",
                width: 400,
                type: 'error'
            }, { position: "top right", direction: "down-push" }, 2000);
            return;
        }

        const formData = formInstance.option("formData");
        let year = null, month = null;
        if (formData.period) {
            const date = new Date(formData.period);
            year = date.getFullYear();
            month = date.getMonth() + 1;
        }

        const params = new URLSearchParams();
        if (year) params.append('year', year);
        if (month) params.append('month', month);
        if (formData.area && formData.area.length > 0) {
            formData.area.forEach(area => {
                params.append('area[]', area.value || area);
            });
        }
        if (formData.virtual) params.append('virtual', formData.virtual);

        try {
            const response = await fetch(`${APP_BASE_URL}/actual-working-day/data?${params.toString()}`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const result = await response.json();
            if (!result.success || !result.data || result.data.length === 0) {
                throw new Error(result.message || 'No data found for the selected filters.');
            }

            const grid = $("#reportGrid").dxDataGrid("instance");
            grid.option("dataSource", result.data);

            DevExpress.ui.notify({
                message: `Loaded: ${result.data.length} record(s)`,
                width: 400,
                type: 'success'
            }, { position: "top right", direction: "down-push" }, 3000);
        } catch (err) {
            DevExpress.ui.notify({
                message: `Failed to load data: ${err.message || err}`,
                type: 'error',
                width: 500
            }, { position: "top right", direction: "down-push" }, 3000);
        }
    }
});
</script>

@endsection