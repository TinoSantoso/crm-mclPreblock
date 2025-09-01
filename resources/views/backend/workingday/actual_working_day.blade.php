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
                        <div id="workingday-tabpanel"></div>
                        <div id="exportLoadingPanel"></div>
                    </div>
                </div>
            </section>
        </div>
    </section>
    <style>
        #main-btn{
            float:left;
            margin-top:10px;

            width:100%;
            text-align:left;
        }
        .inner{
            display: inline-block;
        }
    </style>
    <script>
        let flag_add = false;
        let flag_edit = false;
        
        $(function() {
            // Initialize Tab Panel
            $("#workingday-tabpanel").dxTabPanel({
                dataSource: [
                    {
                        title: "Entry",
                        icon: "fa fa-pencil",
                        template: function() {
                            return $(`
                                <div class="container-fluid">
                                    <div class="row" style="padding-bottom: 20px;">
                                        <div class="col-md-10">
                                            <div id='main-btn'>
                                                <div class="inner"><div id="add"></div></div>
                                                <div class="inner"><div id="save"></div></div>
                                                <div class="inner"><div id="edit"></div></div>
                                                <div class="inner"><div id="cancel"></div></div>
                                                <div class="inner"><div id="delete"></div></div>
                                                <div class="inner"><div id="posted"></div></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-bottom: 20px;">
                                        <div class="col-md-12">
                                            <div id="workingday-dxform"></div>
                                        </div>
                                    </div>
                                    <div class="dx-field" style="margin-bottom:20px">
                                        <div id="load" style="margin-top:10px; display: inline-block;"></div>
                                        <div id="export" style="margin-top:10px; display: inline-block;"></div>
                                    </div>
                                    <div id="workingday-grid" style="padding-top:20px"></div>
                                </div>
                            `);
                        }
                    },
                    {
                        title: "List",
                        icon: "fa fa-list",
                        template: function() {
                            return $(`
                                <div class="container-fluid">
                                    
                                    <div id="workingday-list-grid" style="padding-top:20px"></div>
                                </div>
                            `);
                        }
                    }
                ],
                animationEnabled: true,
                swipeEnabled: true,
                tabsPosition: "top",
                stylingMode: "secondary",
                iconPosition: "start",
                selectedIndex: 0,
                onItemRendered: function(e) {
                    if (e.itemIndex === 0) {
                        // Initialize Entry tab components
                        initializeEntryTab();
                    } else if (e.itemIndex === 1) {
                        // Initialize List tab components
                        initializeListTab();
                    }
                }
            });

            function initializeEntryTab() {
                $("#workingday-dxform").dxForm({
                    formData: {
                        period: new Date(),
                        area: [],
                        virtual: "No"
                    },
                    labelLocation: "left",
                    width: '100%',
                    items: [
                        {
                            itemType: "group",
                            colCount: 3,
                            items: [
                                {
                                    dataField: "fwdTransaction",
                                    label: { text: "Trans No" },
                                    editorType: "dxTextBox",
                                    editorOptions: { disabled: true },
                                    isRequired: true
                                },
                                {
                                    dataField: "transaction_date",
                                    label: { text: "Transaction Date" },
                                    editorType: "dxDateBox",
                                    editorOptions: { 
                                        type: "date",
                                        value: new Date(),
                                        width: 'auto',
                                        disabled: true
                                    },
                                    isRequired: true
                                },
                                {
                                    dataField: "remark",
                                    label: { text: "Remark" },
                                    editorType: "dxTextArea",
                                    editorOptions: { 
                                        height: 35,
                                        width: 'auto',
                                        disabled: true
                                    }
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
                                        width: 'auto',
                                        disabled: true,
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
                                    editorType: "dxDropDownBox",
                                    editorOptions: {
                                        value: [],
                                        displayExpr: "text",
                                        valueExpr: "value",
                                        showClearButton: true,
                                        multiline: false,
                                        width: '100%',
                                        disabled: true,
                                        dataSource: [
                                            @foreach($districtAreas as $area)
                                            { text: "{{ $area['text'] }}", value: "{{ $area['value'] }}" },
                                            @endforeach
                                        ],
                                        contentTemplate: function(e) {
                                            const value = e.component.option("value") || [];
                                            const $list = $("<div>").dxList({
                                                dataSource: e.component.option("dataSource"),
                                                displayExpr: "text",
                                                valueExpr: "value",
                                                selectionMode: "multiple",
                                                showSelectionControls: true,
                                                selectedItems: value,
                                                onSelectionChanged: function(arg) {
                                                    const selectedItems = arg.component.option("selectedItems");
                                                    e.component.option("value", selectedItems);
                                                    e.component.option("text", selectedItems.map(item => item.text).join(", "));
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
                                        width: 'auto',
                                        disabled: true,
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
                    disabled: true,
                    onClick: function(e) { 
                        loadData();
                    }
                });

                // Button logic
                $("#add").dxButton({
                    icon: 'fa fa-file-o',
                    text: "Add",
                    width: 110,
                    onClick: function(e) { 
                        AddNew();
                    }
                });
                $("#save").dxButton({
                    icon: 'fa fa-save',
                    text: "Save",
                    disabled: true,
                    useSubmitBehavior: true,
                    width: 110,
                    onClick: function(e) {
                        save();
                    }
                });
                $("#edit").dxButton({
                    icon: 'fa fa-edit',
                    text: "Edit",
                    width: 110,
                    disabled: true,
                    onClick: function(e) {             
                        edit();
                    }
                });
                $("#cancel").dxButton({
                    icon: 'fa fa-times',
                    text: "Cancel",
                    width: 110,
                    disabled: true,
                    onClick: function(e) { cancel(); }
                });
                $("#delete").dxButton({
                    icon: 'fa fa-trash',
                    text: "Delete",
                    width: 110,
                    disabled: true,
                    onClick: function(e) { del(); }
                });
                $("#posted").dxButton({
                    icon: 'fa fa-paper-plane',
                    text: "Posted",
                    width: 110,
                    disabled: true,
                    onClick: function(e) { posted(); }
                });

                $("#workingday-grid").dxDataGrid({
                    dataSource: [],
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
                            fixed: true
                        },
                        {
                            dataField: "",
                            caption: "Final Working Days",
                            dataType: "number",
                            alignment: "left",
                            allowEditing: false,
                            fixed: true,
                            calculateCellValue: function(rowData) {
                                const standardWorkingDays = rowData.standard_working_days || 0;
                                const grandTotal = rowData.final_total_visits || 0;
                                const otherDays = rowData.other_days || 0;
                                const asmAdjustment = rowData.asm_adjustment || 0;
                                const workingDaysWithAdjustment = grandTotal + otherDays + asmAdjustment;
                                
                                return Math.min(standardWorkingDays, workingDaysWithAdjustment);
                            }
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
                            dataField: "",
                            caption: "Working Days with Adjustment",
                            dataType: "number",
                            alignment: "left",
                            allowEditing: false,
                            fixed: true,
                            calculateCellValue: function(rowData) {
                                const grandTotal = rowData.final_total_visits || 0;
                                const otherDays = rowData.other_days || 0;
                                const asmAdjustment = rowData.asm_adjustment || 0;
                                return grandTotal + otherDays + asmAdjustment;
                            }
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
                            headerCellTemplate: function(header, info) {
                                header.append('<div style="background-color: #f8d7da; font-weight: bold;">' + info.column.caption + '</div>');
                            }
                        },
                        {
                            dataField: "note",
                            caption: "Note Adjustment",
                            fixed: true,
                            allowEditing: true,
                            headerCellTemplate: function(header, info) {
                                header.append('<div style="background-color: #f8d7da; font-weight: bold;">' + info.column.caption + '</div>');
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
                            dataField: "final_total_visits",
                            caption: "Grand Total",
                            dataType: "number",
                            alignment: "left",
                            allowEditing: false,
                            fixed: true
                        }
                    ],
                    showBorders: true,
                    showRowLines: true,
                    paging: { pageSize: 20 },
                    filterRow: { visible: false },
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
            }

            function initializeListTab() {
                $("#workingday-list-grid").dxDataGrid({
                    dataSource: [],
                    columns: [
                        {
                            dataField: "transNo",
                            caption: "Transaction No"
                        },
                        {
                            dataField: "transDate",
                            caption: "Transaction Date",
                            dataType: "datetime"
                        },
                        {
                            dataField: "period",
                            caption: "Period",
                            dataType: "date",
                            displayFormat: "yyyy-MM"
                        },
                        {
                            dataField: "area",
                            caption: "Area"
                        },
                        {
                            dataField: "remark",
                            caption: "Remark"
                        },
                        {
                            dataField: "status_record_id",
                            caption: "Status",
                            calculateCellValue: function(rowData) {
                                return rowData.status_record_id === 1 ? "Created" : "Posted";
                            }
                        },
                        {
                            dataField: "created_at",
                            caption: "Created At",
                            dataType: "datetime"
                        }
                    ],
                    showBorders: true,
                    showRowLines: true,
                    paging: { pageSize: 20 },
                    filterRow: { visible: true },
                    height: 'inherit',
                    columnAutoWidth: true,
                    wordWrapEnabled: true,
                    width: '100%',
                    selection: {
                        mode: "single"
                    },
                    onRowDblClick: function(e) {
                        const selectedData = e.data;
                        if (selectedData) {
                            // Switch to entry tab and load selected data
                            $("#workingday-tabpanel").dxTabPanel("instance").option("selectedIndex", 0);
                            loadSelectedRecord(selectedData);
                        }
                    },
                    onContentReady: function(e) {
                        const gridElement = e.element;
                        if (!gridElement.find('.grid-instruction').length) {
                            gridElement.prepend('<div class="grid-instruction" style="color: #FF0000; font-style: italic; margin-top: 5px;">* Double click on a row to edit data</div>');
                        }
                    }
                });
                
                // Load list data
                loadListData();
            }
            
            // Load selected record function
            async function loadSelectedRecord(record) {
                const form = $("#workingday-dxform").dxForm("instance");
                const formData = form.option("formData");
                
                formData.fwdTransaction = record.transNo;
                formData.transaction_date = new Date(record.transDate);
                formData.period = new Date(record.period);
                formData.area = record.area ? [record.area] : [];
                formData.remark = record.remark;
                
                form.option("formData", formData);
                
                // Load combined data (header + details)
                await loadCombinedData(record.transNo);
                
                // Enable appropriate buttons based on record status
                const isPosted = record.status_record_id === 2;
                
                $("#add").dxButton("instance").option("disabled", false);
                $("#save").dxButton("instance").option("disabled", true);
                $("#edit").dxButton("instance").option("disabled", isPosted);
                $("#cancel").dxButton("instance").option("disabled", true);
                $("#delete").dxButton("instance").option("disabled", isPosted);
                $("#posted").dxButton("instance").option("disabled", isPosted);
                $("#load").dxButton("instance").option("disabled", true);
            }
            
            // Load combined data function
            async function loadCombinedData(transNo) {
                try {
                    const response = await fetch(`${APP_BASE_URL}/actual-working-day/fwd-list/${transNo}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.success && result.data.details) {
                            const grid = $("#workingday-grid").dxDataGrid("instance");
                            const workingDayData = result.data.details;
                            
                            if (workingDayData.length > 0) {
                                // Get the period info from the first record to add day columns
                                const firstRecord = workingDayData[0];
                                const year = firstRecord.year;
                                const month = firstRecord.month;
                                const daysInMonth = new Date(year, month, 0).getDate();
                                
                                // Get current columns
                                const currentColumns = grid.option("columns");
                                
                                // Remove any existing day columns
                                const baseColumns = currentColumns.filter(col => !col.dataField || !col.dataField.startsWith('day_'));
                                
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
                                
                                // Update grid columns and data
                                grid.option("columns", [...baseColumns, ...dayColumns]);
                            }
                            
                            grid.option("dataSource", workingDayData);
                        }
                    }
                } catch (error) {
                    console.error('Error loading combined data:', error);
                }
            }
        });

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

        // Load list data function
        async function loadListData() {
            try {
                const response = await fetch(`${APP_BASE_URL}/actual-working-day/fwd-list`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        $("#workingday-list-grid").dxDataGrid("instance").option("dataSource", result.data);
                    }
                }
            } catch (error) {
                console.error('Error loading list data:', error);
            }
        }

        // Add New function to generate transaction number and enable form editing
        async function AddNew() {
            try {
                const response = await fetch(`${APP_BASE_URL}/actual-working-day/generate-transno`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to generate transaction number');
                }

                const data = await response.json();
                const form = $("#workingday-dxform").dxForm("instance");
                const formData = form.option("formData");
                formData.fwdTransaction = data.trans_no;
                formData.area = [];
                form.option("formData", formData);
                
                // Enable form fields for editing
                form.itemOption("fwdTransaction", "editorOptions", { disabled: true });
                form.itemOption("transaction_date", "editorOptions", { disabled: true });
                form.itemOption("period", "editorOptions", { disabled: false });
                form.itemOption("area", "editorOptions", { disabled: false });
                form.itemOption("virtual", "editorOptions", { disabled: false });
                form.itemOption("remark", "editorOptions", { disabled: false });
                
                // Update button states
                $("#add").dxButton("instance").option("disabled", true);
                $("#save").dxButton("instance").option("disabled", false);
                $("#edit").dxButton("instance").option("disabled", true);
                $("#cancel").dxButton("instance").option("disabled", false);
                $("#delete").dxButton("instance").option("disabled", true);
                $("#posted").dxButton("instance").option("disabled", true);
                $("#load").dxButton("instance").option("disabled", false);
                
                // Set flag
                flag_add = true;
                flag_edit = false;
                
            } catch (error) {
                DevExpress.ui.notify({
                    message: `Error generating transaction number: ${error.message}`,
                    type: "error",
                    width: 600
                }, { position: "top right", direction: "down-push" }, 3000);
            }
        }

        // Edit function to enable form editing
        function edit() {
            const form = $("#workingday-dxform").dxForm("instance");
            
            // Enable form fields for editing
            form.itemOption("transaction_date", "editorOptions", { disabled: false });
            form.itemOption("period", "editorOptions", { disabled: false });
            form.itemOption("area", "editorOptions", { disabled: false });
            form.itemOption("virtual", "editorOptions", { disabled: false });
            form.itemOption("remark", "editorOptions", { disabled: false });
            
            // Update button states
            $("#add").dxButton("instance").option("disabled", true);
            $("#save").dxButton("instance").option("disabled", false);
            $("#edit").dxButton("instance").option("disabled", true);
            $("#cancel").dxButton("instance").option("disabled", false);
            $("#delete").dxButton("instance").option("disabled", true);
            $("#posted").dxButton("instance").option("disabled", true);
            $("#load").dxButton("instance").option("disabled", false);
            
            // Set flag
            flag_add = false;
            flag_edit = true;
        }

        // Save function to handle both add and edit modes
        async function save() {
            try {
                const form = $("#workingday-dxform").dxForm("instance");
                if (!form.validate().isValid) {
                    DevExpress.ui.notify({ 
                        message: "Please fill in all required fields.", 
                        width: 500, 
                        type: 'error' 
                    }, { position: "top right", direction: "down-push" }, 2000);
                    return;
                }
                // Check if working day grid is empty
                const gridData = $("#workingday-grid").dxDataGrid("instance").option("dataSource");
                if (!gridData || gridData.length === 0) {
                    DevExpress.ui.notify({ 
                        message: "Should add detail working day.", 
                        width: 500, 
                        type: 'error' 
                    }, { position: "top right", direction: "down-push" }, 2000);
                    return;
                }

                const formData = form.option("formData");
                
                if (flag_add) {
                    // Store new data
                    try {
                        const response = await fetch(`${APP_BASE_URL}/actual-working-day/store`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            body: JSON.stringify({
                                transNo: formData.fwdTransaction,
                                transDate: formData.transaction_date,
                                period: formData.period,
                                area: formData.area,
                                remark: formData.remark,
                                gridData: gridData
                            })
                        });

                        if (!response.ok) {
                            throw new Error('Failed to save working day data');
                        }

                        const result = await response.json();
                        if (!result.success) {
                            throw new Error(result.message || 'Failed to save working day data');
                        }

                    } catch (error) {
                        DevExpress.ui.notify({
                            message: `Error saving data: ${error.message}`,
                            type: "error",
                            width: 600
                        }, { position: "top right", direction: "down-push" }, 3000);
                        return;
                    }
                    
                } else if (flag_edit) {
                    // Update existing data
                    try {
                        const response = await fetch(`${APP_BASE_URL}/actual-working-day/update`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            body: JSON.stringify({
                                transNo: formData.fwdTransaction,
                                transDate: formData.transaction_date,
                                period: formData.period,
                                area: formData.area,
                                remark: formData.remark,
                                gridData: gridData
                            })
                        });

                        if (!response.ok) {
                            throw new Error('Failed to update working day data');
                        }

                        const result = await response.json();
                        if (!result.success) {
                            throw new Error(result.message || 'Failed to update working day data');
                        }

                    } catch (error) {
                        DevExpress.ui.notify({
                            message: `Error updating data: ${error.message}`,
                            type: "error",
                            width: 600
                        }, { position: "top right", direction: "down-push" }, 3000);
                        return;
                    }
                }
                
                const successMsg = flag_add ? "Data saved successfully" : "Data updated successfully";
                
                // Refresh the list grid after successful save/update
                loadListData();
                
                DevExpress.ui.notify({ 
                    message: successMsg, 
                    width: 500, 
                    type: 'success'
                }, { position: "top right", direction: "down-push" }, 3000);
                
                resetFormState();
            } catch (error) {
                DevExpress.ui.notify({
                    message: `Error saving data: ${error.message}`,
                    type: "error",
                    width: 600
                }, { position: "top right", direction: "down-push" }, 3000);
            }
        }

        // Cancel function to reset flags and form state
        function cancel() {
            resetFormState();
            const grid = $("#workingday-grid").dxDataGrid("instance");
            const baseColumns = [
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
                    dataField: "",
                    caption: "Final Working Days",
                    dataType: "number",
                    alignment: "left",
                    allowEditing: false,
                    fixed: true,
                    calculateCellValue: function(rowData) {
                        const standardWorkingDays = rowData.standard_working_days || 0;
                        const grandTotal = rowData.final_total_visits || 0;
                        const otherDays = rowData.other_days || 0;
                        const asmAdjustment = rowData.asm_adjustment || 0;
                        const workingDaysWithAdjustment = grandTotal + otherDays + asmAdjustment;
                        
                        return Math.min(standardWorkingDays, workingDaysWithAdjustment);
                    }
                },
                {
                    dataField: "standard_working_days",
                    caption: "Standard Working Days",
                    dataType: "number",
                    alignment: "left",
                    allowEditing: false,
                    fixed: true,
                },
                {
                    dataField: "",
                    caption: "Working Days with Adjustment",
                    dataType: "number",
                    alignment: "left",
                    allowEditing: false,
                    fixed: true,
                    calculateCellValue: function(rowData) {
                        const grandTotal = rowData.final_total_visits || 0;
                        const otherDays = rowData.other_days || 0;
                        const asmAdjustment = rowData.asm_adjustment || 0;
                        return grandTotal + otherDays + asmAdjustment;
                    }
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
                    headerCellTemplate: function(header, info) {
                        header.append('<div style="background-color: #f8d7da; font-weight: bold;">' + info.column.caption + '</div>');
                    }
                },
                {
                    dataField: "note",
                    caption: "Note Adjustment",
                    fixed: true,
                    allowEditing: true,
                    headerCellTemplate: function(header, info) {
                        header.append('<div style="background-color: #f8d7da; font-weight: bold;">' + info.column.caption + '</div>');
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
                    dataField: "final_total_visits",
                    caption: "Grand Total",
                    dataType: "number",
                    alignment: "left",
                    allowEditing: false,
                    fixed: true
                }
            ];
            
            grid.option("columns", baseColumns);
            grid.option("dataSource", []);
        }

        // Reset form state and button states
        function resetFormState() {
            const form = $("#workingday-dxform").dxForm("instance");
            
            // Disable form fields
            form.itemOption("fwdTransaction", "editorOptions", { disabled: true });
            form.itemOption("transaction_date", "editorOptions", { disabled: true });
            form.itemOption("period", "editorOptions", { disabled: true });
            form.itemOption("area", "editorOptions", { disabled: true });
            form.itemOption("virtual", "editorOptions", { disabled: true });
            form.itemOption("remark", "editorOptions", { disabled: true });
            
            // Reset button states
            $("#add").dxButton("instance").option("disabled", false);
            $("#save").dxButton("instance").option("disabled", true);
            $("#edit").dxButton("instance").option("disabled", true);
            $("#cancel").dxButton("instance").option("disabled", true);
            $("#delete").dxButton("instance").option("disabled", true);
            $("#posted").dxButton("instance").option("disabled", true);
            $("#load").dxButton("instance").option("disabled", true);
            
            // Reset flags
            flag_add = false;
            flag_edit = false;
        }

        // Posted function to update status to posted
        async function posted() {
            try {
                const form = $("#workingday-dxform").dxForm("instance");
                const formData = form.option("formData");
                
                if (!formData.fwdTransaction) {
                    DevExpress.ui.notify({
                        message: "No transaction selected for posting",
                        type: "error",
                        width: 400
                    }, { position: "top right", direction: "down-push" }, 3000);
                    return;
                }

                const response = await fetch(`${APP_BASE_URL}/actual-working-day/posted`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({
                        transNo: formData.fwdTransaction
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to post working day data');
                }

                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.message || 'Failed to post working day data');
                }

                // Refresh the list grid after successful posting
                loadListData();

                DevExpress.ui.notify({
                    message: "Working day data posted successfully",
                    type: "success",
                    width: 500
                }, { position: "top right", direction: "down-push" }, 3000);

                // Reset form state after posting
                resetFormState();

            } catch (error) {
                DevExpress.ui.notify({
                    message: `Error posting data: ${error.message}`,
                    type: "error",
                    width: 600
                }, { position: "top right", direction: "down-push" }, 3000);
            }
        }

        function del() {
            const form = $("#workingday-dxform").dxForm("instance");
            const formData = form.option("formData");
            
            if (!formData.fwdTransaction) {
                DevExpress.ui.notify({ 
                    message: "No transaction selected for deletion.", 
                    width: 400, 
                    type: "warning"
                }, { position: "top right", direction: "down-push" }, 3000);
                return;
            }

            const grid = $("#workingday-grid").dxDataGrid("instance");
            const baseColumns = [
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
                    dataField: "",
                    caption: "Final Working Days",
                    dataType: "number",
                    alignment: "left",
                    allowEditing: false,
                    fixed: true,
                    calculateCellValue: function(rowData) {
                        const standardWorkingDays = rowData.standard_working_days || 0;
                        const grandTotal = rowData.final_total_visits || 0;
                        const otherDays = rowData.other_days || 0;
                        const asmAdjustment = rowData.asm_adjustment || 0;
                        const workingDaysWithAdjustment = grandTotal + otherDays + asmAdjustment;
                        
                        return Math.min(standardWorkingDays, workingDaysWithAdjustment);
                    }
                },
                {
                    dataField: "standard_working_days",
                    caption: "Standard Working Days",
                    dataType: "number",
                    alignment: "left",
                    allowEditing: false,
                    fixed: true,
                },
                {
                    dataField: "",
                    caption: "Working Days with Adjustment",
                    dataType: "number",
                    alignment: "left",
                    allowEditing: false,
                    fixed: true,
                    calculateCellValue: function(rowData) {
                        const grandTotal = rowData.final_total_visits || 0;
                        const otherDays = rowData.other_days || 0;
                        const asmAdjustment = rowData.asm_adjustment || 0;
                        return grandTotal + otherDays + asmAdjustment;
                    }
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
                    headerCellTemplate: function(header, info) {
                        header.append('<div style="background-color: #f8d7da; font-weight: bold;">' + info.column.caption + '</div>');
                    }
                },
                {
                    dataField: "note",
                    caption: "Note Adjustment",
                    fixed: true,
                    allowEditing: true,
                    headerCellTemplate: function(header, info) {
                        header.append('<div style="background-color: #f8d7da; font-weight: bold;">' + info.column.caption + '</div>');
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
                    dataField: "final_total_visits",
                    caption: "Grand Total",
                    dataType: "number",
                    alignment: "left",
                    allowEditing: false,
                    fixed: true
                }
            ];

            bootbox.confirm({
                title: "Delete Confirmation",
                message: `Are you sure you want to delete transaction <strong>${formData.fwdTransaction}</strong>?<br><br>This action cannot be undone.`,
                buttons: {
                    confirm: {
                        label: 'Yes',
                        className: 'btn-danger'
                    },
                    cancel: {
                        label: 'No',
                        className: 'btn-secondary'
                    }
                },
                callback: function(result) {
                    if(result) {
                        $.ajax({
                            url: `${APP_BASE_URL}/actual-working-day/destroy`,
                            method: 'DELETE',
                            contentType: 'application/json',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: JSON.stringify({
                                transNo: formData.fwdTransaction
                            }),
                            success: function(res) {
                                if(res.success) {
                                    DevExpress.ui.notify({ 
                                        message: `Working day data deleted successfully!`, 
                                        width: 500, 
                                        type: 'success'
                                    }, { position: "top right", direction: "down-push" }, 3000);
                                    
                                    // Refresh the list grid after successful deletion
                                    loadListData();
                                    
                                    // Reset form state after deletion
                                    resetFormState();
                                    grid.option("columns", baseColumns);
                                    grid.option("dataSource", []);
                                } else {
                                    DevExpress.ui.notify({ 
                                        message: res.message || 'Failed to delete working day data', 
                                        width: 500, 
                                        type: 'error'
                                    }, { position: "top right", direction: "down-push" }, 3000);
                                }
                            },
                            error: function(xhr, status, error) {
                                let errorMessage = 'Failed to delete working day data';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                DevExpress.ui.notify({ 
                                    message: errorMessage, 
                                    width: 500, 
                                    type: 'error'
                                }, { position: "top right", direction: "down-push" }, 3000);
                            }
                        });
                    }
                }
            });
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
            if (formData.area && formData.area.length > 0) {
                formData.area.forEach(area => {
                    params.append('area[]', area.value || area);
                });
            }
            if (formData.virtual) params.append('virtual', formData.virtual);
            
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
                const processedData = response.data;
                grid.option("dataSource", processedData);
                
                // Set filename for export based on period and area
                const periodText = `${year}-${String(month).padStart(2, '0')}`;
                const areaText = formData.area && formData.area.length > 0 
                    ? formData.area.map(area => (area.text || area).replace(/[^a-zA-Z0-9]/g, '_')).join('_') 
                    : 'All_District';
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
                    message: `Failed to load data: ${err.message || err}`, 
                    type: 'error', 
                    width: 500
                }, { position: "top right", direction: "down-push" }, 3000);
            } finally {
                $("#exportLoadingPanel").dxLoadPanel("instance").option("visible", false);
            }
        }
    </script>

@stop