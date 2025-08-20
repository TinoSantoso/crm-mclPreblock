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
                                        { text: "All District", value: "" },
                                        { text: "Northern Sumatra", value: "Northern Sumatra" },
                                        { text: "Southern Sumatra", value: "Southern Sumatra" },
                                        { text: "Western Jakarta", value: "Western Jakarta" },
                                        { text: "Eastern Jakarta", value: "Eastern Jakarta" },
                                        { text: "West Java", value: "West Java" },
                                        { text: "Kalimantan", value: "Kalimantan" },
                                        { text: "Northern Central Java", value: "Northern Central Java" },
                                        { text: "Southern Central Java", value: "Southern Central Java" },
                                        { text: "Northern East Java", value: "Northern East Java" },
                                        { text: "Southern East Java", value: "Southern East Java" },
                                        { text: "Bali Nusra", value: "Bali Nusra" },
                                        { text: "Far East", value: "Far East" }
                                    ],
                                    value: "",
                                    displayExpr: "text",
                                    valueExpr: "value",
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

            /* $("#export").dxButton({
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
            }); */

            $("#workingday-grid").dxDataGrid({
                dataSource: [],
                columns: [
                    {
                        dataField: "area",
                        caption: "Area",
                        fixed: true
                    },
                    { 
                        dataField: "employee_name", 
                        caption: "Employee Name",
                        fixed: true
                    },
                    { 
                        dataField: "employee_id", 
                        caption: "NIK Essity",
                        fixed: true
                    },
                    {
                        dataField: "final_total_visits",
                        caption: "Final Working Days",
                        dataType: "number",
                        alignment: "left",
                        allowEditing: false,
                        fixed: true
                    },
                    {
                        dataField: "",
                        caption: "Standard Working Days",
                        dataType: "number",
                        alignment: "left",
                        fixed: true,
                        calculateCellValue: function() {
                            return 19;
                        }
                    },
                    {
                        dataField: "standard_working_days",
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
                        fixed: true,
                        allowEditing: true,
                        validationRules: [{
                            type: "numeric"
                        }],
                        setCellValue: async function(rowData, value, currentRowData) {
                            // Allow both positive and negative numbers
                            const adjustmentValue = value || 0;
                            
                            try {
                                await updateAdjustmentData(rowData, currentRowData, adjustmentValue, null);
                            } catch (error) {
                                // Error handling is already done in the reusable function
                                console.error('Failed to update adjustment:', error);
                            }
                        }
                    },
                    {
                        dataField: "note",
                        caption: "Note Adjustment",
                        fixed: true,
                        allowEditing: true,
                        setCellValue: async function(rowData, value, currentRowData) {
                            let noteValue = value;
                            
                            // Apply default note logic if needed
                            if (currentRowData.adjustment_from_asm && currentRowData.adjustment_from_asm !== 0 && !value) {
                                noteValue = "Adjustment made by ASM";
                            }
                            
                            try {
                                await updateAdjustmentData(rowData, currentRowData, null, noteValue);
                            } catch (error) {
                                // Error handling is already done in the reusable function
                                console.error('Failed to update note:', error);
                            }
                        }
                    },
                    {
                        dataField: "other_days",
                        caption: "BR/Training/Event",
                        dataType: "number",
                        alignment: "left",
                        allowEditing: false,
                        fixed: true,
                        calculateCellValue: function() {
                            return 3;
                        }
                    },
                    {
                        dataField: "grand_total",
                        caption: "Grand Total",
                        dataType: "number",
                        alignment: "left",
                        allowEditing: false,
                        fixed: true,
                        calculateCellValue: function(rowData) {
                            return (rowData.standard_working_days || 0) - 3;
                        }
                    }
                ],
                showBorders: true,
                showRowLines: true,
                paging: { pageSize: 20 },
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
                    allowExportSelectedData: false
                },
                onExporting: function(e) {
                    e.cancel = true;
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

        // Reusable function to update adjustment and note via API
        async function updateAdjustmentData(rowData, currentRowData, adjustmentValue = null, noteValue = null) {
            try {
                const visit_id = currentRowData.id;
                
                if (!visit_id) {
                    console.error('Available currentRowData keys:', Object.keys(currentRowData || {}));
                    throw new Error('Visit ID not found in row data');
                }

                // Calculate base working days for the current month
                function calculateBaseWorkingDays() {
                    const form = $("#workingday-dxform").dxForm("instance");
                    const formData = form.option("formData");
                    if (formData.period) {
                        const date = new Date(formData.period);
                        const year = date.getFullYear();
                        const month = date.getMonth() + 1;
                        const daysInMonth = new Date(year, month, 0).getDate();
                        
                        // Count working days (excluding weekends)
                        let workingDays = 0;
                        for (let i = 1; i <= daysInMonth; i++) {
                            const dayDate = new Date(year, month - 1, i);
                            const dayOfWeek = dayDate.getDay();
                            if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                                workingDays++;
                            }
                        }
                        return workingDays;
                    }
                    return 19; // fallback
                }

                // Use existing values if not provided
                const finalAdjustmentValue = adjustmentValue !== null ? adjustmentValue : (currentRowData.asm_adjustment || 0);
                const finalNoteValue = noteValue !== null ? noteValue : (currentRowData.note || '');
                const baseWorkingDays = calculateBaseWorkingDays();
                
                const response = await fetch(`${APP_BASE_URL}/actual-working-day/update-adjustment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({
                        id: visit_id,
                        adjustment_value: finalAdjustmentValue,
                        note: finalNoteValue,
                        working_days: baseWorkingDays
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to update adjustment data');
                }

                // Update the row data after successful server update
                rowData.asm_adjustment = finalAdjustmentValue;
                rowData.note = finalNoteValue;
                rowData.final_total_visits = (currentRowData.total_offline_visits + currentRowData.total_online_visits) + finalAdjustmentValue;
                rowData.standard_working_days = baseWorkingDays + finalAdjustmentValue;
                
                $("#workingday-grid").dxDataGrid("instance").refresh();

                DevExpress.ui.notify({
                    message: "Data updated successfully",
                    type: "success",
                    width: 600
                }, { position: "top right", direction: "down-push" }, 2000);
            } catch (error) {
                DevExpress.ui.notify({
                    message: `Error updating data: ${error.message}`,
                    type: "error",
                    width: 600
                }, { position: "top right", direction: "down-push" }, 3000);
                
                // Re-throw the error so calling code can handle it
                throw error;
            }
        }

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
                            allowEditing: false,
                            width: 50
                        });
                    }
                }
                
                // Update grid columns
                grid.option("columns", [...baseColumns, ...dayColumns]);
                
                // The data is already processed by the controller to include day columns
                const processedData = response.data;
                grid.option("dataSource", processedData);
                
                // Set filename for export based on period and area
                const periodText = `${year}-${String(month).padStart(2, '0')}`;
                const areaText = formData.area ? `${formData.area.replace(/[^a-zA-Z0-9]/g, '_')}` : 'All_District';
                const fileName = `Working Days ${areaText}_${periodText}.xlsx`;
                
                // Update grid export configuration
                grid.option("onExporting", function(e) {
                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet('Working Days');
                    
                    DevExpress.excelExporter.exportDataGrid({
                        component: e.component,
                        worksheet: worksheet,
                        autoFilterEnabled: true
                    }).then(function() {
                        workbook.xlsx.writeBuffer().then(function(buffer) {
                            saveAs(new Blob([buffer], { type: 'application/octet-stream' }), fileName);
                        });
                    });
                    e.cancel = true;
                });
                
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