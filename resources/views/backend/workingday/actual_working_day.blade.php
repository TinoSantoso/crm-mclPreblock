@extends('layouts.backend')
@section('content')

    <section class="content-header">
        <h1>
            Actual Working Day
            <small>Management</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="#"><i class="fa fa-calendar"></i> Working Day</a></li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <section class="col-md-12 col-lg-12 connectedSortable">
                <div class="box box-danger box-solid">
                    <div class="box-header with-border">
                        <h3 id="bartitle" class="box-title">Actual Working Day</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="dx-field">
                            <div class="dx-field-value" style="float:left">
                                <div id="workingday-dxform"></div>
                            </div>
                        </div>
                        <div class="dx-field" style="margin-bottom:20px">
                            <div id="load" style="margin-top:10px; display: inline-block;"></div>
                            <div id="export" style="margin-top:10px; display: inline-block; margin-left: 10px;"></div>
                        </div>
                        <div id="exportLoadingPanel"></div>
                        <div id="workingday-grid" style="padding-top:20px"></div>
                    </div>
                </div>
            </section>
        </div>
    </section>
    <script>
        $(function() {
            $("#workingday-dxform").dxForm({
                formData: {
                    period: new Date(),
                    area: null,
                    virtual: "No"
                },
                labelLocation: "left",
                items: [
                    {
                        itemType: "group",
                        colCount: 3,
                        items: [
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
                                    width: 'auto',
                                    calendarOptions: {
                                        maxZoomLevel: "year",
                                        minZoomLevel: "year"
                                    }
                                },
                                isRequired: true
                            },
                            {
                                dataField: "area",
                                label: { text: "Area" },
                                editorType: "dxSelectBox",
                                editorOptions: {
                                    items: [
                                        "Northern Sumatra",
                                        "Southern Sumatra", 
                                        "Western Sumatra",
                                        "Eastern Jakarta",
                                        "West Java",
                                        "Kalimantan",
                                        "Northern Central Java",
                                        "Southern Central Java",
                                        "Northern East Java",
                                        "Southern East Java",
                                        "Bali Nusra",
                                        "Far East"
                                    ],
                                    searchEnabled: true,
                                    width: 'auto'
                                },
                                isRequired: false
                            },
                            {
                                dataField: "virtual",
                                label: { text: "Virtual" },
                                editorType: "dxSelectBox",
                                editorOptions: {
                                    items: ["Yes", "No"],
                                    width: 'auto'
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
                type: 'normal',
                stylingMode: 'outlined',
                width: '15vw',
                onClick: function(e) { 
                    loadData();
                }
            });

            $("#export").dxButton({
                icon: 'fa fa-file-excel-o',
                text: "Export to Excel",
                type: 'normal',
                stylingMode: 'outlined',
                onClick: async function(e) {
                    DevExpress.ui.notify({
                        message: "Export feature not implemented yet",
                        type: "warning"
                    }, { position: "top right", direction: "down-push" }, 3000);
                }
            });

            $("#workingday-grid").dxDataGrid({
                dataSource: [],
                columns: [
                    {
                        dataField: "area",
                        caption: "Area",
                        fixed: true
                    },
                    { 
                        dataField: "employee_id", 
                        caption: "Employee ID",
                        fixed: true
                    },
                    { 
                        dataField: "employee_name", 
                        caption: "Employee Name",
                        fixed: true
                    },
                    {
                        dataField: "final_total_visits",
                        caption: "Final Working Days",
                        dataType: "number",
                        alignment: "left",
                        fixed: true
                    },
                    {
                        dataField: "standard_working_days",
                        caption: "Standard Working Days",
                        dataType: "number",
                        alignment: "left",
                        fixed: true
                    },
                    {
                        dataField: "final_total_visits",
                        caption: "Working Days with Adjustment",
                        dataType: "number",
                        alignment: "left",
                        fixed: true
                    },
                    {
                        dataField: "adjustment_from_asm",
                        caption: "Adjustment from ASM",
                        dataType: "number",
                        alignment: "left",
                        fixed: true,
                        allowEditing: true,
                        setCellValue: function(rowData, value) {
                            rowData.adjustment_from_asm = value;
                            rowData.final_total_visits = (rowData.total_offline_visits + rowData.total_online_visits) + value;
                        }
                    },
                    {
                        dataField: "note_adjustment",
                        caption: "Note Adjustment",
                        fixed: true,
                        allowEditing: true,
                        setCellValue: function(rowData, value) {
                            rowData.note_adjustment = value;
                            // Auto-populate note if adjustment is not 0
                            if (rowData.adjustment_from_asm && rowData.adjustment_from_asm !== 0 && !value) {
                                rowData.note_adjustment = "Adjustment made by ASM";
                            }
                        }
                    },
                    {
                        dataField: "final_total_visits",
                        caption: "Grand Total",
                        dataType: "number",
                        alignment: "left",
                        fixed: true
                    }
                ],
                showBorders: true,
                showRowLines: true,
                paging: { pageSize: 10 },
                filterRow: { visible: false },
                searchPanel: { visible: true, width: 240, placeholder: 'Search...' },
                height: 'inherit',
                columnAutoWidth: true,
                wordWrapEnabled: true,
                editing: {
                    mode: "cell",
                    allowUpdating: true
                },
                scrolling: {
                    mode: "standard",
                    showScrollbar: "always"
                },
                export: {
                    enabled: true,
                    fileName: "Actual Working Days",
                    allowExportSelectedData: true
                },
                summary: {
                    totalItems: [
                        {
                            column: "employee_id",
                            summaryType: "count",
                            displayFormat: "Total: {0} rows"
                        },
                    ]
                }
            });

            $("#exportLoadingPanel").dxLoadPanel({
                message: "Loading, please wait...",
                visible: false,
                shadingColor: "rgba(0,0,0,0.4)",
                width: 300,
                height: 100,
                showIndicator: true,
                showPane: true,
                shading: true,
                hideOnOutsideClick: false
            });
        });

        async function loadData() {
            const form = $("#workingday-dxform").dxForm("instance");
            if (!form.validate().isValid) {
                DevExpress.ui.notify({ 
                    message: "Please fill in all required fields.", 
                    width: 400, 
                    type: 'error' 
                }, { position: "top right", direction: "down-push" }, 2000);
                return;
            }
            
            const formData = form.option("formData");
            let year = null, month = null;
            if (formData.period) {
                const date = new Date(formData.period);
                year = date.getFullYear();
                month = date.getMonth() + 1;
            }
            
            const params = new URLSearchParams();
            if (year) params.append('year', year);
            if (month) params.append('month', month);
            if (formData.area) params.append('area', formData.area);
            
            $("#exportLoadingPanel").dxLoadPanel("instance").option("visible", true);
            
            try {
                const res = await fetch(`${APP_BASE_URL}/actual-working-day/data?${params.toString()}`);
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                
                const response = await res.json();
                if (!response.success || !response.data || response.data.length === 0) {
                    throw new Error(response.message || 'No data found for the selected filters.');
                }

                // Get days in month
                const daysInMonth = new Date(year, month, 0).getDate();
                
                // Add day columns dynamically
                const grid = $("#workingday-grid").dxDataGrid("instance");
                const currentColumns = grid.option("columns");
                
                // Remove any existing day columns
                const baseColumns = currentColumns.filter(col => !col.dataField.startsWith('day_'));
                
                // Generate day columns for working days only
                const dayColumns = [];
                for (let i = 1; i <= daysInMonth; i++) {
                    const date = new Date(year, month - 1, i);
                    const dayOfWeek = date.getDay();
                    
                    // Skip weekends (0 = Sunday, 6 = Saturday)
                    if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                        dayColumns.push({
                            dataField: `day_${i}`,
                            caption: `${i}`,
                            dataType: "number",
                            alignment: "center",
                            width: 50
                        });
                    }
                }
                
                // Update grid columns
                grid.option("columns", [...baseColumns, ...dayColumns]);
                
                // The data is already processed by the controller to include day columns
                const processedData = response.data;
                
                grid.option("dataSource", processedData);
                
                DevExpress.ui.notify({ 
                    message: `Loaded: ${response.data.length} record(s)`, 
                    width: 400, 
                    type: 'success'
                }, { position: "top right", direction: "down-push" }, 3000);
            } catch (err) {
                DevExpress.ui.notify({ 
                    message: `Error loading data: ${err.message || err}`, 
                    type: 'error', 
                    width: 500
                }, { position: "top right", direction: "down-push" }, 3000);
            } finally {
                $("#exportLoadingPanel").dxLoadPanel("instance").option("visible", false);
            }
        }
    </script>

@stop