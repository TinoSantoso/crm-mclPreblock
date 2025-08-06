@extends('layouts.backend')
@section('content')

<section class="content-header">
      <h1>
        Report Preblock MCL
        <small>CRM</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>home</a></li>
        <li><a href="#"><i class="fa fa-bar-chart"></i>report</a></li>
      </ol>
</section>

<section class="content">
    <div class="row">
        <section class="col-md-12 col-lg-12 connectedSortable">
                <div class="box box-info box-solid">
                    <div class="box-header with-border">
                            <h3 id="bartitle" class="box-title">MCL Preblock Report</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                                </button>
                            </div>
                    </div>
                    <div class="box-body">
                            <div class="row" style="margin-bottom: 20px;">
                                <div class="col-md-12">
                                    <div id="report-dxform"></div>
                                    <div style="margin-top: 20px;">
                                        <div class="inner"><div id="load"></div></div>
                                        <div class="inner" style="margin-left: 10px;"><div id="export"></div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-bottom: 20px;">
                                <div class="col-md-12">
                                    <div id="report-grid"></div>
                                </div>
                            </div>
                    </div>
                </div>
        </section>
    </div>
    <div id="loadPanel"></div>
</section>

<style>
    .inner{
        display: inline-block;
    }
    #report-dxform {
        margin-top: 20px;
    }
    .dx-button.dx-button-success {
        background-color: #5cb85c;
    }
    .dx-button.dx-button-warning {
        background-color: #f0ad4e;
    }
    .summary-item {
        margin-bottom: 10px;
        padding: 10px;
        background-color: #f9f9f9;
        border-left: 3px solid #3c8dbc;
    }
    .visit-date-header {
        font-size: 16px;
        font-weight: bold;
        margin: 10px 0;
        padding: 5px;
        background-color: #f4f4f4;
        border-bottom: 1px solid #ddd;
    }
</style>

{{-- Scripts for this page --}}
<script>
$(function() {
    $("#loadPanel").dxLoadPanel({
        message: "Loading data, please wait...",
        visible: false,
        shadingColor: "rgba(0,0,0,0.4)",
        showIndicator: true,
        showPane: true,
        shading: true,
        hideOnOutsideClick: false
    });

    $("#report-dxform").dxForm({
        formData: {
            period: new Date(),
            employee: null
        },
        labelLocation: "left",
        items: [
            {
                itemType: "group",
                colCount: 1,
                items: [
                    {
                        dataField: "employee",
                        label: { text: "Employee" },
                        editorType: "dxSelectBox",
                        editorOptions: {
                            dataSource: @json($users->map(function($user) {
                                return [
                                    'id' => $user->employee_id,
                                    'text' => $user->name
                                ];
                            })),
                            displayExpr: "text",
                            valueExpr: "id",
                            searchEnabled: true,
                            placeholder: "Select an employee",
                            width: '20vw'
                        },
                        isRequired: true
                    },
                    {
                        dataField: "period",
                        label: { text: "Period" },
                        editorType: "dxDateBox",
                        editorOptions: { 
                            type: "date",
                            displayFormat: "yyyy-MM",
                            pickerType: "calendar",
                            useMaskBehavior: true,
                            openOnFieldClick: true,
                            width: '20vw',
                            calendarOptions: {
                                maxZoomLevel: "year",
                                minZoomLevel: "year"
                            }
                        },
                        isRequired: true
                    }
                ]
            }
        ]
    });

    $("#load").dxButton({
        icon: 'refresh',
        text: "View Report",
        type: 'default',
        stylingMode: 'contained',
        width: '10vw',
        onClick: function(e) { 
            loadReportData();
        }
    });
});

async function loadReportData() {
    const form = $("#report-dxform").dxForm("instance");
    if (!form.validate().isValid) {
        DevExpress.ui.notify({ message: "Please fill in all required fields.", width: 400, type: 'error' }, { position: "top right", direction: "down-push" }, 2000);
        return;
    }

    const formData = form.option("formData");
    if (!formData.employee) {
        DevExpress.ui.notify({ message: "Employee is required.", width: 400, type: 'error' }, { position: "top right", direction: "down-push" }, 2000);
        return;
    }
    if (!formData.period) {
        DevExpress.ui.notify({ message: "Period is required.", width: 400, type: 'error' }, { position: "top right", direction: "down-push" }, 2000);
        return;
    }

    const date = new Date(formData.period);
    const period = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
    const employee = formData.employee;
    
    $("#loadPanel").dxLoadPanel("instance").option("visible", true);
    
    try {
        const res = await fetch(`${APP_BASE_URL}/report-preblock-visit/data?empId=${employee}&period=${period}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        
        const response = await res.json();
        if (response.status !== 'success') {
            throw new Error(response.message || 'Failed to load data');
        }
        
        // Process the nested data structure to create a flat array for the grid
        const flattenedData = [];
        
        if (response.data && response.data.length > 0) {
            response.data.forEach(visit => {
                if (visit.details && visit.details.length > 0) {
                    visit.details.forEach(detail => {
                        flattenedData.push({
                            id: detail.id,
                            trans_no: detail.trans_no,
                            account: detail.account,
                            contact: detail.contact,
                            category: detail.cat,
                            visit_call: detail.vf,
                            class: detail.class,
                            visit_date: detail.visit_date,
                            remark: detail.remark
                        });
                    });
                }
            });
        }

        $("#report-grid").dxDataGrid({
            dataSource: flattenedData,
            columns: [
                { dataField: "account", caption: "Account", dataType: "string", allowEditing: false},
                { dataField: "category", caption: "Category", dataType: "string", allowEditing: false },
                { dataField: "contact", caption: "Contact Name", dataType: "string", allowEditing: false },
                { dataField: "visit_call", caption: "Target Call", dataType: "number", alignment: "left", allowEditing: false },
                { dataField: "class", caption: "Specialty", dataType: "string", allowEditing: false },
                {
                    dataField: "visit_date",
                    caption: "Visit date",
                    dataType: "date",
                    allowEditing: true,
                    editorType: "dxDateBox",
                    editorOptions: {
                        type: "date",
                        displayFormat: "yyyy-MM-dd",
                        pickerType: "calendar",
                        useMaskBehavior: true,
                        openOnFieldClick: true,
                        height: 30,
                    },
                    validationRules: [{ type: "required" }]
                },
                {
                    dataField: "remark",
                    caption: "Remark",
                    dataType: "string",
                    allowEditing: true,
                    editorType: "dxTextArea",
                    editorOptions: { height: 30 }
                }
            ],
            showBorders: true,
            showRowLines: true,
            rowAlternationEnabled: true,
            paging: { pageSize: 25 },
            filterRow: { visible: true },
            searchPanel: { visible: true, width: 240, placeholder: 'Search...' },
            height: 'auto',
            columnAutoWidth: true,
            export: {
                enabled: flattenedData.length > 0,
                allowExportSelectedData: false,
                fileName: `report-${period}`,
                format: 'xlsx'
            },
            onExporting: function(e) {
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Report');
                
                DevExpress.excelExporter.exportDataGrid({
                    component: e.component,
                    worksheet: worksheet,
                    autoFilterEnabled: true
                }).then(function() {
                    workbook.xlsx.writeBuffer().then(function(buffer) {
                        saveAs(new Blob([buffer], { type: 'application/octet-stream' }), `report-${period}.xlsx`);
                    });
                });
                e.cancel = true;
            },
            summary: {
                totalItems: [
                    {
                        column: "id",
                        summaryType: "count",
                        displayFormat: "Total: {0} visits"
                    }
                ]
            }
        });

        if (!flattenedData.length) {
            DevExpress.ui.notify({ 
                message: `${response.message}`, 
                width: 400, 
                type: 'warning'
            }, { position: "top right", direction: "down-push" }, 3000);
        } else {
            DevExpress.ui.notify({ 
                message: `Successfully loaded data for period: ${response.period} - ${flattenedData.length} visits`, 
                width: 400, 
                type: 'success'
            }, { position: "top right", direction: "down-push" }, 3000);
        }
    } catch (error) {
        console.error("Error loading report data:", error);
        DevExpress.ui.notify({ 
            message: `Error loading data: ${error.message || 'Unknown error'}`, 
            width: 400, 
            type: 'error'
        }, { position: "top right", direction: "down-push" }, 4000);
    } finally {
        $("#loadPanel").dxLoadPanel("instance").option("visible", false);
    }
}

</script>
@stop