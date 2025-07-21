$(function() {
    $("#visit-dxform").dxForm({
        formData: {
            employeeID: '',
            period: '',
            visit_date: null
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
                        dataField: "visit_date",
                        label: { text: "Visit Date" },
                        editorType: "dxDateBox",
                        editorOptions: {
                            type: "date",
                            useMaskBehavior: true,
                            openOnFieldClick: true,
                            width: 'auto',
                        },
                        isRequired: true
                    }
                ]
            }
        ]
    });

    // Button logic
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
});

async function loadData() {
    const form = $("#visit-dxform").dxForm("instance");
    if (!form.validate().isValid) {
        DevExpress.ui.notify({ message: "Please fill in all required fields.", width: 400, type: 'error' }, { position: "top right", direction: "down-push" }, 2000);
        return;
    }
    const formData = form.option("formData");
    // Extract year and month from period
    let year = null, month = null;
    if (formData.period) {
        const date = new Date(formData.period);
        year = date.getFullYear();
        month = date.getMonth() + 1;
    }
    // Format visit_date as yyyy-mm-dd (use selected date directly, no timezone conversion)
    let visit_date = null;
    if (formData.visit_date) {
        const d = new Date(formData.visit_date);
        visit_date = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    // Build query params
    const params = new URLSearchParams();
    if (year) params.append('year', year);
    if (month) params.append('month', month);
    if (visit_date) params.append('visit_date', visit_date);

    $("#exportLoadingPanel").dxLoadPanel({
        message: "Exporting, please wait...",
        visible: false,
        shadingColor: "rgba(0,0,0,0.4)",
        width: 300,
        height: 100,
        showIndicator: true,
        showPane: true,
        shading: true,
        hideOnOutsideClick: false
    });
    // Show loading panel
    $("#exportLoadingPanel").dxLoadPanel("instance").option("visible", true);
    
    try {
        const res = await fetch(`${APP_BASE_URL}/crm-visits?${params.toString()}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        
        const response = await res.json();
        if (!response.data || response.data.length === 0) {
            throw new Error(response.message || 'No data found for the selected filters.');
        }
        const data = response.data;
        let gridData = data;
        
        // Map month number to month name
        const monthNames = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        gridData = gridData.map(item => ({
            ...item,
            month: item.month ? monthNames[item.month - 1] : ""
        }));

        $("#visit-grid").dxDataGrid({
            dataSource: gridData,
            columns: [
            { dataField: "trans_no", caption: "Trans No" },
            { dataField: "emp_id", caption: "Employee ID" },
            { dataField: "year", caption: "Year", alignment: "left" },
            { dataField: "month", caption: "Month", alignment: "left" },
            { dataField: "remark", caption: "Remark" }
            ],
            showBorders: true,
            showRowLines: true,
            paging: { pageSize: 10 },
            filterRow: { visible: true },
            searchPanel: { visible: true, width: 240, placeholder: 'Search...' },
            height: 'inherit',
            columnAutoWidth: true,
            masterDetail: {
            enabled: true,
            template: function(container, options) {
                    const details = Array.isArray(options.data.details) ? options.data.details : [];
                    $('<div>').dxDataGrid({
                        dataSource: details,
                        columns: [
                            {
                                dataField: 'is_visited',
                                caption: 'Visited',
                                dataType: 'boolean',
                                width: 80,
                                allowEditing: true,
                                cellTemplate: function(cellElement, cellInfo) {
                                    const value = cellInfo.value ? true : false;
                                    const grid = cellInfo.component;
                                    $('<div>').dxCheckBox({
                                        value: value,
                                        onValueChanged: function(e) {
                                            if (e.value !== value) {
                                                cellInfo.data.is_visited = e.value ? 1 : 0;
                                                grid.refresh();
                                                const detailId = cellInfo.data.id;
                                                fetch(`${APP_BASE_URL}/crm-visit-detail/${detailId}/is-visited`, {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-Requested-With': 'XMLHttpRequest',
                                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                    },
                                                    body: JSON.stringify({ is_visited: e.value ? 1 : 0 })
                                                })
                                                .then(res => res.json())
                                                .then(resp => {
                                                    if (resp.status === 'success') {
                                                        DevExpress.ui.notify({ message: 'Status updated', width: 300, type: 'success' }, { position: 'top right', direction: 'down-push' }, 2000);
                                                    } else {
                                                        DevExpress.ui.notify({ message: resp.error || 'Update failed', type: 'error' }, { position: 'top right', direction: 'down-push' }, 2000);
                                                    }
                                                })
                                                .catch(() => {
                                                    DevExpress.ui.notify({ message: 'Update failed', type: 'error' }, { position: 'top right', direction: 'down-push' }, 2000);
                                                });
                                            }
                                        }
                                    }).appendTo(cellElement);
                                }
                            },
                            { dataField: 'account', caption: 'Account' },
                            { dataField: 'contact', caption: 'Contact' },
                            { dataField: 'visit_date', caption: 'Visit Date', dataType: 'date' },
                            { dataField: 'cat', caption: 'Category' },
                            { dataField: 'vf', caption: 'Target Call', alignment: 'left' },
                            { dataField: 'class', caption: 'Specialty' },
                            { dataField: 'remark', caption: 'Remark' },
                        ],
                        editing: {
                            mode: 'cell',
                            allowUpdating: true,
                            allowAdding: false,
                            allowDeleting: false,
                            useIcons: true
                        },
                        showBorders: true,
                        showRowLines: true,
                        filterRow: { visible: true },
                        paging: { enabled: true, pageIndex: 0, pageSize: 25 },
                        columnAutoWidth: true,
                        height: 'inherit',
                        summary: {
                            totalItems: [{
                                column: "account",
                                summaryType: "count",
                                displayFormat: "Total: {0} rows"
                            }]
                        },
                    }).appendTo(container);
                }
            }
        });
        DevExpress.ui.notify({ message: `Loaded: ${gridData.length} record(s)`, width: 400, type: 'success'}, { position: "top right", direction: "down-push" }, 3000);
    } catch (err) {
        DevExpress.ui.notify({ message: `Error loading data: ${err.message || err}`, type: 'error', width: 500}, { position: "top right", direction: "down-push" }, 3000);
    } finally {
        $("#exportLoadingPanel").dxLoadPanel("instance").option("visible", false);
    }
}