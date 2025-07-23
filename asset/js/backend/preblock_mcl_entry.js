$(function() {
    // Header dxForm
    window.headerDxForm = $("#header-dxform").dxForm({
        formData: {},
        colCount: 2,
        items: [
            {
                dataField: "trans_no",
                label: { text: "Trans No" },
                editorType: "dxTextBox",
                editorOptions: { disabled: true },
                colSpan: 1,
                isRequired: true
            },
            {
                dataField: "transaction_date",
                label: { text: "Transaction Date" },
                editorType: "dxDateBox",
                editorOptions: { 
                    type: "date",
                    value: new Date(),
                    disabled: true 
                },
                colSpan: 1,
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
                    disabled: true,
                    calendarOptions: {
                        maxZoomLevel: "year",
                        minZoomLevel: "year"
                    }
                },
                colSpan: 1,
                isRequired: true
            },
            {
                dataField: "remark",
                label: { text: "Remark" },
                editorType: "dxTextArea",
                colSpan: 1,
                editorOptions: { height: 35, disabled: true }
            }
        ]
    }).dxForm("instance");

    // institusi-grid: empty by default
    $("#institusi-grid").dxDataGrid({
        dataSource: [],
        editing: {
            mode: "cell",
            allowUpdating: false,
            allowAdding: false,
            allowDeleting: false,
            useIcons: true
        },
        columns: [
            { 
                dataField: "institusi", 
                caption: "Account", 
                dataType: "string", 
                allowEditing: false
            },
            { dataField: "cat", caption: "Category", dataType: "string", allowEditing: false },
            { dataField: "individu", caption: "Contact Name", dataType: "string", allowEditing: false },
            { dataField: "vf", caption: "Target Call", dataType: "number", alignment: "left", allowEditing: false },
            { dataField: "class", caption: "Specialty", dataType: "string", allowEditing: false },
            {
                dataField: "period",
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
            },
        ],
        showBorders: true,
        showRowLines: true,
        paging: { enabled: true, pageIndex: 0, pageSize: 25 },
        pager: { showPageSizeSelector: true, allowedPageSizes: [25, 50, 100] },
        filterRow: { visible: true },
        searchPanel: { visible: true, width: 240, placeholder: 'Search...' },
        height: 'inherit',
        columnAutoWidth: true,
        summary: {
            totalItems: [{
                column: "institusi",
                summaryType: "count",
                displayFormat: "Total: {0} rows"
            }]
        },
        onToolbarPreparing: function(e) {
            e.toolbarOptions.items.unshift({
                location: "after",
                widget: "dxButton",
                options: {
                    icon: "plus",
                    text: "Add Details",
                    visible: false, // Disabled by default
                    onClick: function() {
                    // Add popup container to the DOM if not present
                    if ($('#popup').length === 0) {
                        $('body').append('<div id="popup"></div>');
                    }
                    const popupContent = BootboxContent();
                    const $popup = $("#popup");
                    $popup.empty();
                    $popup.dxPopup({
                        title: 'Select Account(s)',
                        width: '90vw',
                        height: '100%',
                        showCloseButton: true,
                        dragEnabled: true,
                        hideOnOutsideClick: false,
                        contentTemplate: function() {
                        return popupContent;
                        },
                        visible: true,
                        toolbarItems: [
                        {
                            widget: 'dxButton',
                            toolbar: 'bottom',
                            location: 'after',
                            options: {
                                text: 'Apply',
                                type: 'success',
                                onClick: function() {
                                    const dataGridSource = $("#listVisit").dxDataGrid("instance");
                                    const dataGridTarget = $("#institusi-grid").dxDataGrid("instance");
                                    let selectedRows = dataGridSource.getSelectedRowsData();
                                    if(selectedRows.length > 0) {
                                        
                                        $popup.dxPopup('instance').hide();
                                    } else {
                                        DevExpress.ui.notify({ message: 'Please select a data to apply.', width: 400, type: "warning"}, { position: "top right", direction: "down-push" }, 3000);
                                    }
                                }
                            }
                        },
                        {
                            widget: 'dxButton',
                            toolbar: 'bottom',
                            location: 'after',
                            options: {
                                text: 'Cancel',
                                onClick: function() {
                                    $popup.dxPopup('instance').hide();
                                }
                            }
                        }
                        ]
                    });
                    }
                }
            });
        },
    });

});

function BootboxContent() {
    // Add a dxSelectBox to choose a date value (1 to 31) for the current month
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth(); // 0-based
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    const dateOptions = [];
    for (let day = 1; day <= daysInMonth; day++) {
        const value = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const text = value;
        dateOptions.push({ value, text });
    }

    let frm_str = `
        <form id="some-form">
            <div class='form-group' style="display: flex; flex-direction: column; gap: 20px;">
                <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px; justify-content: flex-end;">
                    <label for="date-selectbox" style="margin-right: 5px;">Select Date:</label>
                    <div id="date-selectbox" style="min-width: 200px;"></div>
                </div>
                <div style="display: flex; gap: 20px; align-items: stretch;">
                    <div id="listAcc" style="flex: 1 1 0;"></div>
                    <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 10px; min-width: 60px;">
                        <div style="flex: 1;"></div>
                        <button type="button" id="btn-move-right" style="width: 40px; height: 40px; font-size: 20px; margin-bottom: 10px;">&#8594;</button>
                        <button type="button" id="btn-move-left" style="width: 40px; height: 40px; font-size: 20px;">&#8592;</button>
                        <div style="flex: 1;"></div>
                    </div>
                    <div id="listVisit" style="flex: 1 1 0; display: flex; flex-direction: column;"></div>
                </div>
            </div>
        </form>
    `;

    let object = $('<div/>').html(frm_str).contents();
    const cStore = new DevExpress.data.CustomStore({
        load: function (loadOptions) {
            const d = $.Deferred();
            // Get period value from header-dxform
            let period = null;
            if (window.headerDxForm) {
                const formData = window.headerDxForm.option('formData') || {};
                period = formData.period;
            }
            let periodParam = '';
            if (period) {
                let dateObj = (Object.prototype.toString.call(period) === '[object Date]') ? period : new Date(period);
                if (!isNaN(dateObj)) {
                    const y = dateObj.getFullYear();
                    const m = String(dateObj.getMonth() + 1).padStart(2, '0');
                    const d2 = String(dateObj.getDate()).padStart(2, '0');
                    periodParam = `?period=${m}-${d2}-${y}`;
                } else if (typeof period === 'string') {
                    periodParam = `?period=${encodeURIComponent(period)}`;
                }
            }
            
            $.getJSON(`${APP_BASE_URL}/crm-details${periodParam}`, d.resolve);
            return d.promise();
        }
    });

    // --- Data structure to store visit data by date ---
    let visitDataByDate = {};
    dateOptions.forEach(opt => { visitDataByDate[opt.value] = []; });

    // Right grid: listVisit (initially empty)
    const listVisitGrid = object.find("#listVisit").dxDataGrid({
        dataSource: [],
        allowColumnReordering: true,
        allowColumnResizing: true,
        columnAutoWidth: true,
        hoverStateEnabled: true,
        showBorders: false,
        showRowLines: true,
        selection: { 
            mode: "multiple",
            showCheckBoxesMode: "always"
        },
        paging: { enabled: true, pageIndex: 0, pageSize: 20 },
        remoteOperations: false,
        columns: [
            { dataField: "account", caption: "Account", fixed: true, fixedPosition: 'left' },
            { dataField: "contact_name", caption: "Contact", fixed: true, fixedPosition: 'left' },
            { dataField: "category", caption: "Category" },
            { dataField: "target_call", caption: "Target call", alignment: "left", dataType: "number" },
            { dataField: "specialty", caption: "Specialty" }
        ],
        filterRow: { visible: true },
        width: "100%",
        scrolling: { columnRenderingMode: "virtual" }
    }).dxDataGrid('instance');

    // Left grid: listAcc (loaded from cStore)
    object.find("#listAcc").dxDataGrid({
        dataSource: cStore,
        allowColumnReordering: true,
        allowColumnResizing: true,
        columnAutoWidth: true,
        hoverStateEnabled: true,
        showBorders: false,
        showRowLines: true,
        selection: { 
            mode: "multiple",
            showCheckBoxesMode: "always"
        },
        paging: { enabled: true, pageIndex: 0, pageSize: 20 },
        remoteOperations: false,
        columnFixing: {
            enabled: true,
        },
        columns: [
            { dataField: "account", caption: "Account", fixed: true, fixedPosition: "left" },
            { dataField: "contact_name", caption: "Contact", fixed: true, fixedPosition: "left" },
            { dataField: "category", caption: "Category" },
            { dataField: "target_call", caption: "Target call", alignment: "left", dataType: "number" },
            { dataField: "specialty", caption: "Specialty" }
        ],
        filterRow: { visible: true },
        width: "100%",
        scrolling: { columnRenderingMode: "virtual" }
    });

    setTimeout(function() {
        // Initialize date-selectbox and handle on change
        object.find('#date-selectbox').dxSelectBox({
            dataSource: dateOptions,
            displayExpr: "text",
            valueExpr: "value",
            value: dateOptions[0].value,
            onValueChanged: function(e) {
                const selectedDate = e.value;
                listVisitGrid.option('dataSource', visitDataByDate[selectedDate] || []);
                listVisitGrid.refresh();
            }
        });
        // Initial load for first date
        listVisitGrid.option('dataSource', visitDataByDate[dateOptions[0].value] || []);
        listVisitGrid.refresh();
        // Move left: from listVisit to listAcc
        object.find('#btn-move-left').on('click', function() {
            const visitGrid = object.find('#listVisit').dxDataGrid('instance');
            const accGrid = object.find('#listAcc').dxDataGrid('instance');
            const selectedDate = object.find('#date-selectbox').dxSelectBox('instance').option('value');
            visitGrid.getDataSource().store().load().done(function(visitData) {
                accGrid.getDataSource().store().load().done(function(accData) {
                    const selected = visitGrid.getSelectedRowsData();
                    if (selected.length > 0) {
                        const updatedVisitData = visitData.filter(row => !selected.some(sel => sel.account === row.account));
                        const updatedAccData = [...accData];
                        selected.forEach(function(row) {
                            if (!updatedAccData.some(r => r.account === row.account && r.contact_name === row.contact_name && r.category === row.category && r.target_call === row.target_call && r.specialty === row.specialty)) {
                                updatedAccData.push(row);
                            }
                        });
                        visitDataByDate[selectedDate] = updatedVisitData;
                        visitGrid.option('dataSource', updatedVisitData);
                        accGrid.option('dataSource', updatedAccData);
                        visitGrid.refresh();
                        accGrid.refresh();
                        visitGrid.clearSelection();
                    }
                });
            });
        });
        // Move right: from listAcc to listVisit (for selected date)
        object.find('#btn-move-right').on('click', function() {
            const accGrid = object.find('#listAcc').dxDataGrid('instance');
            const visitGrid = object.find('#listVisit').dxDataGrid('instance');
            const selectedDate = object.find('#date-selectbox').dxSelectBox('instance').option('value');
            accGrid.getDataSource().store().load().done(function(accData) {
                visitGrid.getDataSource().store().load().done(function(visitData) {
                    const selected = accGrid.getSelectedRowsData();
                    if (selected.length > 0) {
                        const updatedAccData = accData.filter(row => !selected.some(sel => sel.account === row.account));
                        const updatedVisitData = [...visitData];
                        selected.forEach(function(row) {
                            if (!updatedVisitData.some(r => 
                                r.account === row.account &&
                                r.contact_name === row.contact_name &&
                                r.category === row.category &&
                                r.target_call === row.target_call &&
                                r.specialty === row.specialty
                            )) {
                                updatedVisitData.push(row);
                            }
                        });
                        visitDataByDate[selectedDate] = updatedVisitData;
                        accGrid.option('dataSource', updatedAccData);
                        visitGrid.option('dataSource', updatedVisitData);
                        accGrid.refresh();
                        visitGrid.refresh();
                        accGrid.clearSelection();
                    }
                });
            });
        });
        // Overwrite the Apply button logic for the popup
        object.closest('body').off('click', '.dx-popup-bottom .dx-button.dx-button-success').on('click', '.dx-popup-bottom .dx-button.dx-button-success', function() {
            const dataGridTarget = $("#institusi-grid").dxDataGrid("instance");
            // Get all rows from all pages (not just current page)
            let allRows = [];
            if (Array.isArray(dataGridTarget.option('dataSource'))) {
                allRows = dataGridTarget.option('dataSource');
            } else if (typeof dataGridTarget.getDataSource === 'function' && typeof dataGridTarget.getDataSource().items === 'function') {
                allRows = dataGridTarget.getDataSource().items();
            }
            let appended = Array.isArray(allRows) ? [...allRows] : [];
            
            // For each date, get all rows in visitDataByDate and add to institusi-grid with period set
            let allRowsSource = [];
            Object.entries(visitDataByDate).forEach(([date, rows]) => {
                rows.forEach(row => {
                    // Map to institusi-grid format and set period to date
                    const mapped = {
                        institusi: row.account,
                        cat: row.category,
                        individu: row.contact_name,
                        vf: row.target_call,
                        class: row.specialty,
                        period: date,
                        remark: ''
                    };
                    allRowsSource.push(mapped);
                });
            });
            
            // Only append rows with (institusi, cat, period) that do not already exist in the grid
            let appendedRows = Array.isArray(allRows) ? [...allRows] : [];
            // Always deduplicate the new batch itself, and then REPLACE any existing row in the grid with the new one (not skip)
            const seenKeys = new Set();
            const uniqueNewRows = allRowsSource.filter(row => {
                const key = `${row.institusi}|${row.cat}|${row.individu}|${row.period}`;
                if (!seenKeys.has(key)) {
                    seenKeys.add(key);
                    return true;
                }
                return false;
            });
            // Build a map of all existing rows, then replace with new batch (so new always wins)
            const rowMap = new Map();
            appendedRows.forEach(row => {
                const key = `${row.institusi}|${row.cat}|${row.individu}|${row.period}`;
                rowMap.set(key, row);
            });
            uniqueNewRows.forEach(row => {
                const key = `${row.institusi}|${row.cat}|${row.individu}|${row.period}`;
                rowMap.set(key, row); // always replace with new
            });
            const mergedRows = Array.from(rowMap.values());
            dataGridTarget.option('dataSource', mergedRows);
            dataGridTarget.refresh();
        });
    }, 0);

    return object;
}
