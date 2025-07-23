$(function() {
    // Tab List: dxDataGrid for crm_visits
    $("#list-panel").dxDataGrid({
        dataSource: new DevExpress.data.CustomStore({
            load: function() {
                return $.ajax({
                    url: `${APP_BASE_URL}/crm-visits`,
                    method: 'GET',
                    dataType: 'json'
                }).then(function(result) {
                    return result;
                }).catch(function() {
                    return [];
                });
            }
        }),
        columns: [
            { dataField: "trans_no", caption: "Trans No" },
            { dataField: "emp_id", caption: "Employee ID" },
            { 
                dataField: "month", 
                caption: "Month",
                alignment: "left",
                customizeText: function(cellInfo) {
                    const val = cellInfo.value;
                    if (!val) return '';
                    const monthNum = Number(val);
                    if (monthNum >= 1 && monthNum <= 12) {
                        return new Date(0, monthNum - 1).toLocaleString('default', { month: 'long' });
                    }
                    return val;
                }
            },
            { dataField: "year", caption: "Year", alignment: "left" },
            { dataField: "remark", caption: "Remark" },
        ],
        selection: { mode: "single" },
        showBorders: true,
        showRowLines: true,
        paging: { enabled: true, pageIndex: 0, pageSize: 25 },
        filterRow: { visible: true },
        searchPanel: { visible: false, width: 240, placeholder: 'Search...' },
        height: 'inherit',
        columnAutoWidth: true,
        summary: {
            totalItems: [{
                column: "trans_no",
                summaryType: "count",
                displayFormat: "Total: {0} rows"
            }]
        },
        onRowDblClick: function(e) {
            applyDataToForm(e.data);
        }
    });

    // Add Apply dxButton above #list-panel
    const applyBtnContainer = document.createElement('div');
    applyBtnContainer.style.marginTop = '10px';
    applyBtnContainer.style.marginBottom = '10px';
    applyBtnContainer.style.display = 'flex';
    applyBtnContainer.style.flexDirection = 'column';
    applyBtnContainer.style.gap = '10px';
    applyBtnContainer.innerHTML = `
        <div id="show-list-panel-btn"></div>
        <div style="color: #FF0000; font-style: italic; margin-top: 5px;">* Double click on a row to edit data or click Show details button to view schedule</div>
    `;
    document.querySelector('#list-panel').insertAdjacentElement('beforebegin', applyBtnContainer);

    $("#show-list-panel-btn").dxButton({
        text: "Show details",
        type: "default",
        icon: "event",
        width: '10vw',
        onClick: function() {
            // Show popup-scheduler and create dxScheduler
            if ("#popup-scheduler" && $("#popup-scheduler").data("dxPopup")) {
                $("#popup-scheduler").dxPopup("dispose");
                $("#popup-scheduler").empty();
            }
            $('#popup-scheduler').show().empty().append('<div id="visit-scheduler"></div>');

            // Get selected row from list-panel
            const grid = $("#list-panel").dxDataGrid("instance");
            const selectedRows = grid.getSelectedRowsData();
            if (selectedRows.length > 0) {
                const selected = selectedRows[0];
                // Prepare scheduler data from details, grouped by visit_date (or period)
                let detailsArr = [];
                if (Array.isArray(selected.details)) {
                    detailsArr = selected.details;
                } else if (selected.details && typeof selected.details === 'object') {
                    detailsArr = Object.values(selected.details);
                }
                
                // Show popup with dxScheduler
                $("#popup-scheduler").dxPopup({
                    title: 'Visit Scheduler',
                    width: '90vw',
                    height: '90vh',
                    showCloseButton: true,
                    dragEnabled: true,
                    hideOnOutsideClick: true,
                    contentTemplate: function() {
                        setTimeout(function() {
                            const mappedSchedulerData = detailsArr.map(event => {
                                let title = event.account || event.title || "No Title";
                                let fullTitle = `${event.account} - ${event.contact}`;
                                let contact = event.contact || "";
                                let startDate = null;
                                let endDate = null;
                                if (event.visit_date) {
                                    const dateObj = new Date(event.visit_date);
                                    if (!isNaN(dateObj.getTime())) {
                                        startDate = new Date(dateObj.getTime());
                                        startDate.setHours(9, 0, 0, 0);
                                        
                                        endDate = new Date(dateObj.getTime());
                                        endDate.setHours(12, 0, 0, 0);
                                    }
                                }
                                return {
                                    title, fullTitle, startDate, endDate, contact
                                };
                            });

                            let schedulerInstance = $("#visit-scheduler").dxScheduler({
                                timeZone: "Asia/Jakarta",
                                dataSource: mappedSchedulerData,
                                textExpr: "fullTitle",
                                views: ["month"],
                                useDropDownViewSwitcher: true,
                                adaptivityEnabled: true,
                                currentView: "month",
                                startDayHour: 9,
                                endDayHour: 17,
                                startDateExpr: "startDate",
                                endDateExpr: "endDate",
                                height: "100%",
                                width: "100%",
                                editing: {
                                    allowAdding: false,
                                    allowUpdating: false,
                                    allowDeleting: false,
                                },
                                onAppointmentClick: function(e) {
                                    e.cancel = true;
                                    // Use the selected row data instead of appointment data
                                    headerDxForm.itemOption("trans_no", "editorOptions", { disabled: true });
                                    headerDxForm.itemOption("transaction_date", "editorOptions", { disabled: true });
                                    headerDxForm.itemOption("period", "editorOptions", { 
                                        disabled: true,
                                        displayFormat: "yyyy-MM",
                                        dateSerializationFormat: "yyyy-MM"
                                    });
                                    headerDxForm.itemOption("remark", "editorOptions", { disabled: true });
                                    headerDxForm.option('formData', {
                                        trans_no: selected.trans_no,
                                        transaction_date: selected.created_at ? new Date(selected.created_at) : null,
                                        period: selected.year && selected.month ? new Date(selected.year, selected.month - 1, 1) : null,
                                        remark: selected.remark
                                    });
                                    headerDxForm.repaint();

                                    // Ensure details is a usable array
                                    let detailsArr = [];
                                    if (Array.isArray(selected.details)) {
                                        detailsArr = selected.details;
                                    } else if (selected.details && typeof selected.details === 'object') {
                                        detailsArr = Object.values(selected.details);
                                    }

                                    // Map details to the format for 'institusi-grid'
                                    const institusiRows = detailsArr.map(row => ({
                                        id: row.id || null,
                                        institusi: row.account || '',
                                        cat: row.cat || '',
                                        individu: row.contact || '',
                                        vf: row.vf,
                                        class: row.class || '',
                                        period: row.visit_date ? (typeof row.visit_date === 'string' ? new Date(row.visit_date) : row.visit_date) : null,
                                        remark: row.remark || ''
                                    }));

                                    const institusiGrid = $("#institusi-grid").dxDataGrid("instance");
                                    institusiGrid.option({
                                        dataSource: institusiRows,
                                        showRowLines: true,
                                        editing: {
                                            allowUpdating: false,
                                            allowAdding: false,
                                            allowDeleting: false,
                                        },
                                        noDataText: ""
                                    });
                                    institusiGrid.refresh();

                                    // Switch to the 'Ent' tab
                                    $('.tab-pane').removeClass('active show');
                                    $('.nav-tabs li').removeClass('active');
                                    $("#Ent.tab-pane").addClass("active show");
                                    const entTab = $('.nav-tabs a[href="#Ent"]');
                                    if (entTab.length) {
                                        entTab.parent().addClass('active');
                                    }
                                    localStorage.setItem('preblock_mcl_active_tab', 'Ent');

                                    // Update button states
                                    $('#add').dxButton('instance').option('disabled', false);
                                    $('#save').dxButton('instance').option('disabled', true);
                                    $('#cancel').dxButton('instance').option('disabled', true);
                                    $('#delete').dxButton('instance').option('disabled', false);
                                    $('#edit').dxButton('instance').option('disabled', false);
                                    $("#export").dxButton("instance").option("disabled", false);

                                    $("#popup-scheduler").dxPopup("hide");
                                    $("#visit-scheduler").dxScheduler("dispose");

                                    DevExpress.ui.notify({
                                        message: 'Data applied to form',
                                        width: 500,
                                        type: 'info'
                                    }, {
                                        position: "top right",
                                        direction: "down-push"
                                    }, 3000);

                                    // --- Your new logic ends here ---
                                },
                                onAppointmentFormOpening: function(e) {
                                    e.cancel = true;
                                },
                                onAppointmentTooltipShowing: function(e) {
                                    const tooltipContainer = document.body;
                                    const colorizeMarkers = function() {
                                        $('.dx-tooltip-appointment-item-marker-body').css('background-color', '#ffa94d');
                                    };

                                    colorizeMarkers();
                                    if (!window._dxTooltipMarkerObserver) {
                                        window._dxTooltipMarkerObserver = new MutationObserver(colorizeMarkers);
                                        window._dxTooltipMarkerObserver.observe(tooltipContainer, { childList: true, subtree: true });
                                    }

                                    // Prevent tooltip from hiding when clicked
                                    $(document).on('click.keepTooltip', '.dx-scheduler-appointment-tooltip', function(e) {
                                        e.stopPropagation();
                                    });
                                }
                            }).dxScheduler("instance");

                            setTimeout(function() {
                                if (schedulerInstance) {
                                    const today = new Date();
                                    schedulerInstance.option("currentDate", new Date(today.getFullYear(), today.getMonth(), 1));
                                    schedulerInstance.reload();
                                    schedulerInstance.repaint();
                                }
                            }, 100);
                            
                        }, 0);
                        return $("<div id='visit-scheduler'></div>");
                    },
                    visible: true
                });
            } else {
                DevExpress.ui.notify({ message: 'No row selected in List panel', width: 500, type: 'warning'}, { position: "top right", direction: "down-push" }, 3000);
            }
        }
    });

    function applyDataToForm(data) {
        // Disable form fields
        headerDxForm.itemOption("trans_no", "editorOptions", { disabled: true });
        headerDxForm.itemOption("transaction_date", "editorOptions", { disabled: true });
        headerDxForm.itemOption("period", "editorOptions", { 
            disabled: true,
            displayFormat: "yyyy-MM",
            dateSerializationFormat: "yyyy-MM"
        });
        headerDxForm.itemOption("remark", "editorOptions", { disabled: true });

        // Set form data from selected row
        headerDxForm.option('formData', {
            trans_no: data.trans_no,
            transaction_date: data.created_at ? new Date(data.created_at) : null,
            period: data.year && data.month ? new Date(data.year, data.month - 1, 1) : null,
            remark: data.remark
        });
        headerDxForm.repaint();

        // Process details data
        let detailsArr = [];
        if (Array.isArray(data.details)) {
            detailsArr = data.details;
        } else if (data.details && typeof data.details === 'object') {
            detailsArr = Object.values(data.details);
        }

        // Map details for institusi grid
        const institusiRows = detailsArr.map(row => ({
            id: row.id || null,
            institusi: row.account || '',
            cat: row.cat || '',
            individu: row.contact || '',
            vf: row.vf,
            class: row.class || '',
            period: row.visit_date ? (typeof row.visit_date === 'string' ? new Date(row.visit_date) : row.visit_date) : null,
            remark: row.remark || ''
        }));

        // Update institusi grid
        const institusiGrid = $("#institusi-grid").dxDataGrid("instance");
        institusiGrid.option({
            dataSource: institusiRows,
            showRowLines: true,
            editing: {
                allowUpdating: false,
                allowAdding: false,
                allowDeleting: false,
            },
            noDataText: ""
        });
        institusiGrid.refresh();

        // Switch to Ent tab
        $('.tab-pane').removeClass('active show');
        $('.nav-tabs li').removeClass('active');
        $("#Ent.tab-pane").addClass("active show");
        const entTab = $('.nav-tabs a[href="#Ent"]');
        if (entTab.length) {
            entTab.parent().addClass('active');
        }
        localStorage.setItem('preblock_mcl_active_tab', 'Ent');

        // Update button states
        $('#add').dxButton('instance').option('disabled', false);
        $('#save').dxButton('instance').option('disabled', true);
        $('#cancel').dxButton('instance').option('disabled', true);
        $('#delete').dxButton('instance').option('disabled', false);
        $('#edit').dxButton('instance').option('disabled', false);
        $("#export").dxButton("instance").option("disabled", false);

        // Show notification
        DevExpress.ui.notify({
            message: 'Data applied to form',
            width: 500,
            type: 'info'
        }, {
            position: "top right",
            direction: "down-push"
        }, 3000);
    }
});