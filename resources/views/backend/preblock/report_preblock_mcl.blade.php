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
        text: "Load Data",
        type: 'default',
        stylingMode: 'contained',
        width: '120px',
        onClick: function(e) { 
            loadReportData();
        }
    });

    $("#export").dxButton({
        icon: 'fa fa-file-pdf-o',
        text: "Export PDF",
        type: 'success',
        stylingMode: 'contained',
        width: '120px',
        onClick: function(e) { 
            exportReportData();
        }
    });
});

async function loadReportData() {
    const form = $("#report-dxform").dxForm("instance");
    if (!form.validate().isValid) {
        DevExpress.ui.notify({ message: "Please select a period.", width: 400, type: 'error' }, { position: "top right", direction: "down-push" }, 2000);
        return;
    }

    const formData = form.option("formData");
    if (!formData.period) {
        DevExpress.ui.notify({ message: "Period is required.", width: 400, type: 'error' }, { position: "top right", direction: "down-push" }, 2000);
        return;
    }

    const date = new Date(formData.period);
    const period = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
    
    $("#loadPanel").dxLoadPanel("instance").option("visible", true);
    
    try {
        const res = await fetch(`${APP_BASE_URL}/report-preblock-visit/data?period=${period}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        
        const response = await res.json();
        if (response.status !== 'success') {
            throw new Error(response.message || 'Failed to load data');
        }
        
        if (!response.data || response.data.length === 0) {
            DevExpress.ui.notify({ message: "No data found for the selected period.", width: 400, type: 'warning' }, { position: "top right", direction: "down-push" }, 3000);
            $("#report-grid").html('<div class="alert alert-info">No data available for the selected period.</div>');
            return;
        }

        $("#report-grid").dxDataGrid({
            dataSource: response.data,
            columns: [
                { dataField: "visit_date", caption: "Visit Date", dataType: "date", format: "yyyy-MM-dd" },
                { dataField: "total_visits", caption: "Total Visits", alignment: "center", width: 120 }
            ],
            showBorders: true,
            showRowLines: true,
            rowAlternationEnabled: true,
            paging: { pageSize: 10 },
            filterRow: { visible: true },
            searchPanel: { visible: true, width: 240, placeholder: 'Search...' },
            height: 'auto',
            columnAutoWidth: true,
            summary: {
                totalItems: [
                    {
                        column: "total_visits",
                        summaryType: "sum",
                        displayFormat: "Total: {0} visits"
                    }
                ]
            },
            masterDetail: {
                enabled: true,
                template: function(container, options) {
                    const visits = options.data.visits || [];
                    
                    $('<div>').addClass('visit-date-header')
                        .text(`Visit Details for ${options.data.visit_date}`)
                        .appendTo(container);
                    
                    $('<div>').dxDataGrid({
                        dataSource: visits,
                        columns: [
                            { dataField: "account", caption: "Account" },
                            { dataField: "contact", caption: "Contact" },
                            { dataField: "category", caption: "Category" },
                            { dataField: "visit_frequency", caption: "Target Call", alignment: "center" },
                            { dataField: "class", caption: "Specialty" },
                            { dataField: "remark", caption: "Remark" }
                        ],
                        showBorders: true,
                        showRowLines: true,
                        paging: { pageSize: 5 },
                        columnAutoWidth: true,
                        summary: {
                            totalItems: [{
                                column: "account",
                                summaryType: "count",
                                displayFormat: "Total: {0} accounts"
                            }]
                        }
                    }).appendTo(container);
                }
            }
        });

        DevExpress.ui.notify({ 
            message: `Successfully loaded data for period: ${period}`, 
            width: 400, 
            type: 'success'
        }, { position: "top right", direction: "down-push" }, 3000);
        
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

async function exportReportData() {
    const form = $("#report-dxform").dxForm("instance");
    if (!form.validate().isValid) {
        DevExpress.ui.notify({ message: "Please select a period.", width: 400, type: 'error' }, { position: "top right", direction: "down-push" }, 2000);
        return;
    }
    
    const formData = form.option("formData");
    const date = new Date(formData.period);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    
    $("#loadPanel").dxLoadPanel("instance").option("visible", true);
    
    try {
        const response = await fetch(`${APP_BASE_URL}/crm-visits/export-pdf`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ year, month })
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `report-${year}-${month}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        DevExpress.ui.notify({
            message: `Downloading PDF for Period: ${year}-${month}`, 
            width: 400, 
            type: 'success'
        }, { position: 'top right', direction: 'down-push' }, 3000);
        
    } catch (error) {
        DevExpress.ui.notify({ 
            message: 'Error exporting PDF: ' + error.message, 
            width: 400, 
            type: 'error'
        }, { position: 'top right', direction: 'down-push' }, 4000);
    } finally {
        $("#loadPanel").dxLoadPanel("instance").option("visible", false);
    }
}
</script>
@stop