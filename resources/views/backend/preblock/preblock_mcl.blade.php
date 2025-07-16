@extends('layouts.backend')
@section('content')

<section class="content-header">
      <h1>
        Entry
        <small>CRM SAP</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-home"></i>home</a></li>
        <li><a href="#"><i class="fa  fa-book"></i>here</a></li>
      </ol>
</section>

<section class="content">
    <div class=" row">
        <section class="col-md-12 col-lg-12 connectedSortable">
                <div class="box box-info box-solid">
                    <div class="box-header with-border">
                            <h3 id="bartitle" class="box-title">List and Entry</h3>
                            <div class="box-tools pull-right">

                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                                </button>
                            </div>
                            <!-- /.box-tools -->
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">

                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#Ent" data-toggle="tab" >Entry</a></li>
                                <li ><a href="#Lst" data-toggle="tab" >List</a></li>
                            </ul>

                            <div class="tab-content"  id="custom-content-below-tabContent">
                                        <!-- --------------------Tab Entry---------------------- -->
                                        <div id="Ent" class="tab-pane" style=" position: relative; ">
                                            <div class="row" style="padding-bottom: 20px;">
                                                <div class="col-md-10">
                                                        <div id='main-btn' >
                                                            <div class="inner"> <div id="add" ></div></div>
                                                            <div class="inner"> <div id="save"></div></div>
                                                            <div class="inner"> <div id="edit"></div></div>
                                                            <div class="inner"> <div id="cancel"></div></div>
                                                            <div class="inner"> <div id="delete"></div></div>
                                                            <div class="inner"> <div id="export"></div></div>
                                                            <div class="inner"> <div id="upload"></div></div>
                                                        </div>
                                                </div>
                                            </div>

                                            <div class="row" style="margin-bottom: 20px;">
                                                <div class="col-md-12">
                                                    <div id="header-dxform"></div>
                                                </div>
                                            </div>
                                            <div class="row" style="margin-bottom: 20px;">
                                                <div class="col-md-12">
                                                    <div id="institusi-grid"></div>
                                                </div>
                                            </div>

                                            <div id="form-container">
                                                <div id="frm1">
                                                    <div id="form1"></div>
                                                                                            
                                                    <hr>
                                                    <div id="btnLoad" ></div>
                                                    <!--  <div id="note2" style="color:red;margin-top:15px; "></div>   -->

                                                    <div class="row">
                                                            <div id="dtl" style="margin-right: 10px;margin-left:10px"></div>
                                                    </div>
                                                    
                                                    
                                                </div>
                                            </div>

                                                
                                            <div id="summary"></div>
                                        </div>

                                        <!----------------------Tab List------------------------->
                                        
                                        <div id="Lst" class="tab-pane" style=" position: relative; ">
                                            <div id="list-panel"></div>
                                        </div>
                                        
                                        {{-- <div class="loadpanel"></div> --}}
                            </div>


                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
        </section>
    </div>
    <div id="popup" style="display:none;"></div>
    <div id="popup-scheduler" style="display:none;"></div>
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
    #visit-dxform {
        margin-top: 20px;
    }
</style>

{{-- Scripts for this page --}}
<script src="{!! url('asset/js/backend/preblock_mcl_entry.js') !!}"></script>
<script src="{!! url('asset/js/backend/preblock_mcl_list.js') !!}"></script>
<script src="{!! url('asset/js/backend/preblock_mcl_export.js') !!}"></script>
<script>
    // Utility to store/retrieve active tab in localStorage
    function setActiveTab(tabId) {
        localStorage.setItem('preblock_mcl_active_tab', tabId);
    }
    function getActiveTab() {
        return localStorage.getItem('preblock_mcl_active_tab');
    }

    $(document).ready(function() {
        // On page load, activate the tab and pane marked as active or from storage
        const storedTab = getActiveTab();
        let $tabToActivate;
        if (storedTab && $('.nav-tabs a[href="' + storedTab + '"]').length) {
            $tabToActivate = $('.nav-tabs a[href="' + storedTab + '"]');
        } else {
            $tabToActivate = $('.nav-tabs li.active a');
        }
        if ($tabToActivate && $tabToActivate.length) {
            const target = $tabToActivate.attr('href');
            $('.tab-pane').removeClass('active show');
            $('.nav-tabs li').removeClass('active');
            $tabToActivate.parent().addClass('active');
            $(target).addClass('active show');
        }

        // Helper to show 'No data' message for empty grids
        function showNoDataIfEmpty(gridSelector) {
            const grid = $(gridSelector).dxDataGrid('instance');
            if (grid) {
            const data = grid.getDataSource().items();
            if (!data || data.length === 0) {
                grid.option('noDataText', 'No data');
            } else {
                grid.option('noDataText', '');
            }
            }
        }
        // Initial check for both grids
        showNoDataIfEmpty('#institusi-grid');
        showNoDataIfEmpty('#list-panel');

        $('.nav-tabs a').on('click', function(e) {
            e.preventDefault();
            const target = $(this).attr('href');
            // Remove 'active' and 'show' from all tab-panes and nav-tabs
            $('.tab-pane').removeClass('active show');
            $('.nav-tabs li').removeClass('active');
            // Add 'active' and 'show' to the clicked tab and corresponding pane
            $(this).parent().addClass('active');
            $(target).addClass('active show');
            setActiveTab(target);

            // Only refresh and check grid if the tab is now active
            if ($(this).parent().hasClass('active')) {
                if (target === '#Ent') {
                    showNoDataIfEmpty('#institusi-grid');
                    const grid = $('#institusi-grid').dxDataGrid('instance');
                    if (grid) {
                    grid.refresh();
                    }
                }
                if (target === '#Lst') {
                    showNoDataIfEmpty('#list-panel');
                    const grid = $('#list-panel').dxDataGrid('instance');
                    if (grid) {
                    grid.refresh();
                    }
                }
            }
        });

        // Global flags to control save mode
        let flag_add = false;
        let flag_edit = false;

        function enableHeaderDxFormFields() {
            headerDxForm.itemOption("trans_no", "editorOptions", { disabled: false,  readOnly: true });
            headerDxForm.itemOption("transaction_date", "editorOptions", { 
                type: "date",
                value: headerDxForm.option("formData").transaction_date || new Date(),
                disabled: false,
                readOnly: true
            });
            headerDxForm.itemOption("period", "editorOptions", { 
                type: "date",
                value: new Date(),
                displayFormat: "yyyy-MM",
                pickerType: "calendar",
                useMaskBehavior: true,
                openOnFieldClick: true,
                disabled: false,
                calendarOptions: {
                    maxZoomLevel: "year",
                    minZoomLevel: "year"
                }
            });
            headerDxForm.itemOption("remark", "editorOptions", { height: 35, disabled: false });
        }

        function setGeneratedTransNo() {
            $.getJSON(`${APP_BASE_URL}/generate-transno`, function(res) {
                if (res.trans_no) {
                    headerDxForm.option('formData.trans_no', res.trans_no);
                    headerDxForm.repaint();
                }
            });
        }

        function AddNew() {
            headerDxForm.resetValues();
            enableHeaderDxFormFields();
            setGeneratedTransNo();
            flag_add = true;
            flag_edit = false;
            $("#add").dxButton("instance").option("disabled", true);
            $("#edit").dxButton("instance").option("disabled", true);
            $("#delete").dxButton("instance").option("disabled", true);
            $("#save").dxButton("instance").option("disabled", false);
            $("#cancel").dxButton("instance").option("disabled", false);
            $("#export").dxButton("instance").option("disabled", true);

            // Clear institusi-grid data
            const grid = $("#institusi-grid").dxDataGrid("instance");
            grid.option({
                dataSource: [],
                editing: {
                    allowUpdating: true,
                    allowAdding: false,
                    allowDeleting: true,
                    useIcons: true
                },
                noDataText: "No data"
            });
            // Enable Add Details button in toolbar
            const $addDetailsBtn = $("#institusi-grid .dx-toolbar .dx-item-content.dx-toolbar-item-content .dx-button");
            if ($addDetailsBtn.length && $addDetailsBtn.dxButton("instance")) {
                $addDetailsBtn.dxButton("instance").option("visible", true);
            }
            grid.refresh();
        }

        function cancel() {
            headerDxForm.resetValues();
            headerDxForm.itemOption("trans_no", "editorOptions", { disabled: true });
            headerDxForm.itemOption("transaction_date", "editorOptions", { disabled: true });
            headerDxForm.itemOption("period", "editorOptions", { disabled: true });
            headerDxForm.itemOption("remark", "editorOptions", { disabled: true });
            $("#add").dxButton("instance").option("disabled", false);
            $("#edit").dxButton("instance").option("disabled", true);
            $("#delete").dxButton("instance").option("disabled", true);
            $("#save").dxButton("instance").option("disabled", true);
            $("#cancel").dxButton("instance").option("disabled", true);
            $("#export").dxButton("instance").option("disabled", true);
            // Clear institusi-grid data
            const grid = $("#institusi-grid").dxDataGrid("instance");
            grid.option({
                dataSource: [],
                editing: {
                    allowUpdating: false,
                    allowAdding: false,
                    allowDeleting: false,
                    useIcons: true
                },
                noDataText: "No data"
            });
            const $addDetailsBtn = $("#institusi-grid .dx-toolbar .dx-item-content.dx-toolbar-item-content .dx-button");
            if ($addDetailsBtn.length && $addDetailsBtn.dxButton("instance")) {
                $addDetailsBtn.dxButton("instance").option("visible", false);
            }
            grid.refresh();
        }

        function edit() {
            enableHeaderDxFormFields();
            const grid = $("#institusi-grid").dxDataGrid("instance");
            grid.option({
                editing: {
                    allowUpdating: true,
                    allowAdding: false,
                    allowDeleting: true,
                    useIcons: true
                }
            });
            const $addDetailsBtn = $("#institusi-grid .dx-toolbar .dx-item-content.dx-toolbar-item-content .dx-button");
            if ($addDetailsBtn.length && $addDetailsBtn.dxButton("instance")) {
                $addDetailsBtn.dxButton("instance").option("visible", true);
            }
            
            flag_add = false;
            flag_edit = true;
            $('#add').dxButton('instance').option('disabled',true);
            $('#save').dxButton('instance').option('disabled',false);
            $('#cancel').dxButton('instance').option('disabled',false);
            $('#delete').dxButton('instance').option('disabled',true);
            $('#edit').dxButton('instance').option('disabled',true);
            $("#export").dxButton("instance").option("disabled", false);
        }

        function del() {
            // Confirm before delete
            const headerForm = $("#header-dxform").dxForm("instance");
            const headerData = headerForm ? headerForm.option("formData") : {};
            if (!headerData.trans_no) {
                DevExpress.ui.notify({ message: "No data selected to delete.", width: 400, type: "warning"}, { position: "top right", direction: "down-push" }, 3000);
                return;
            }
            bootbox.confirm({
                title: "Delete Confirmation",
                message: `Are you sure you want to delete this data?`,
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
                            url: `${APP_BASE_URL}/destroy/${encodeURIComponent(headerData.trans_no)}`,
                            method: 'DELETE',
                            contentType: 'application/json',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                if(res.status === 'success') {
                                    DevExpress.ui.notify({ message: `Data deleted successfully!`, width: 500, type: 'success'}, { position: "top right", direction: "down-push" }, 5000);
                                    cancel();
                                    $("#list-panel").dxDataGrid("instance").getDataSource().reload();
                                } else {
                                    DevExpress.ui.notify({ message: `Failed to delete data: ${res.error || 'Unknown error'}`, width: 500, type: 'error'}, { position: "top right", direction: "down-push" }, 5000);
                                }
                            },
                            error: function(xhr) {
                                DevExpress.ui.notify({ message: `Error: ${xhr.statusText}`, width: 500, type: 'error'}, { position: "top right", direction: "down-push" }, 5000);
                            }
                        });
                    }
                }
            });
        }

        function save() {
            // Validate headerDxForm required fields
            let headerForm = $("#header-dxform").dxForm("instance");
            if (!headerForm.validate().isValid) {
                DevExpress.ui.notify({ message: "Please fill all required fields in the form", width: 500, type: "error"}, { position: "top right", direction: "down-push" }, 3000);
                return;
            }
            // Get the instance of the institusi-grid DataGrid
            let institusiGrid = $("#institusi-grid").dxDataGrid("instance");
            institusiGrid.saveEditData();
            // Get all rows from the grid, not just the current page
            institusiGrid.getDataSource().store().load().done(function(allRows) {
                if (!allRows.length) {
                    DevExpress.ui.notify({ message: "Please add at least one row in the table.", width: 500, type: "error"}, { position: "top right", direction: "down-push" }, 3000);
                    return;
                }
                // Validate that each row in allRows has a valid 'period' (visit date)
                const invalidRow = allRows.find(row => !row.period || isNaN(new Date(row.period).getTime()));
                if (invalidRow) {
                    DevExpress.ui.notify({ 
                        message: `Please enter a valid Visit date`, 
                        width: 500, 
                        type: "error"
                    }, { position: "top right", direction: "down-push" }, 3000);
                    return;
                }

                let headerData = headerForm ? headerForm.option("formData") : {};
                (async () => {
                    try {
                        let url = `${APP_BASE_URL}/store`;
                        let method = 'POST';
                        let successMsg = 'Data saved successfully!';
                        if (flag_edit) {
                            url = `${APP_BASE_URL}/update`;
                            successMsg = 'Data updated successfully!';
                        }
                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            body: JSON.stringify({
                                header: headerData,
                                details: allRows
                            })
                        });
                        const res = await response.json();
                        if (res.status === 'success') {
                            DevExpress.ui.notify({ message: successMsg, width: 500, type: 'success'}, { position: "top right", direction: "down-push" }, 5000);
                            // Clear headerForm & institusi-grid data
                            headerDxForm.itemOption("trans_no", "editorOptions", { disabled: true });
                            headerDxForm.itemOption("transaction_date", "editorOptions", { disabled: true });
                            headerDxForm.itemOption("period", "editorOptions", { disabled: true });
                            headerDxForm.itemOption("remark", "editorOptions", { disabled: true });
                            $("#add").dxButton("instance").option("disabled", false);
                            $("#save").dxButton("instance").option("disabled", true);
                            $("#cancel").dxButton("instance").option("disabled", true);
                            $("#delete").dxButton("instance").option("disabled", false);
                            $("#edit").dxButton("instance").option("disabled", false);
                            $("#export").dxButton("instance").option("disabled", false);
                            const grid = $("#institusi-grid").dxDataGrid("instance");
                            grid.option({
                                editing: {
                                    allowUpdating: false,
                                    allowAdding: false,
                                    allowDeleting: false
                                }
                            });
                            grid.refresh();
                            // Refresh the list-panel grid after successful save
                            $("#list-panel").dxDataGrid("instance").getDataSource().reload();
                            flag_add = false;
                            flag_edit = false;
                        } else {
                            throw new Error('Failed to save data: ' + (res.error || 'Unknown error'));
                        }
                    } catch (err) {
                        DevExpress.ui.notify({ message: err, width: 500, type: 'error'}, { position: "top right", direction: "down-push" }, 5000);
                    }
                })();
            });
        }

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
        $("#export").dxButton({
            icon: 'fa fa-file-pdf-o',
            text: "Export",
            width: 110,
            disabled: true,
            onClick: function(e) { exportData(); }
        });
    });
</script>

@stop