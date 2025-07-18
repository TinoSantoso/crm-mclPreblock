@extends('layouts.backend')
@section('content')

    <section class="content-header">
        <h1>
            Report Sales
            <small>Panel</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-home"></i>home</a></li>
            <li><a href="#"><i class="fa fa-truck"></i>here</a></li>
        </ol>
    </section>

    <section class="content">
        <div class=" row">
            <section class="col-md-12 col-lg-12 connectedSortable">
                <div class="box box-danger box-solid">
                    <div class="box-header with-border">
                        <h3 id="bartitle" class="box-title">List and Entry</h3>
                        <div class="box-tools pull-right">

                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i>
                            </button>
                        </div>
                        <!-- /.box-tools -->
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="dx-field">
                            {{-- <div class="dx-fieldset-header">Please Select :</div> --}}
                            <div class="dx-field-value" style="float:left">
                              <div id="filterForm"></div>
                            </div>

                        </div>
                        <div class="dx-field" style="margin-bottom:20px">
                          <div id="proses" style="margin-top:10px; display: inline-block;"></div>
                          <div id="exportByDistrict" style="margin-top:10px; display: inline-block; margin-left: 10px;"></div>
                        </div>
                        <div id="autoExpand" style="margin-top:10px"></div>
                        <div id="gridContainer" style="padding-top:20px"></div>

                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </section>
        </div>
    </section>
    <script>
            $(document).ready(function() {
                const now = new Date();
                let listData = [];
                let select = 1;

                const areaOptions = [
                  "Northern Sumatra",
                  "Bali Nusra",
                  "Easter Jakarta",
                  "Ecommerce",
                  "Far East",
                  "Kalimantan",
                  "Northern East Java",
                  "Northern Central Java",
                  "West Java",
                  "Western Jakarta",
                  "Southern East Java",
                  "Southern Central Java",
                  "Southern Sumatra"
                ];

                $("#filterForm").dxForm({
                    formData: {
                        period: now,
                        district: []
                    },
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
                                width: "20vw",
                                type: "date",
                                elementAttr: {
                                    style: "margin-bottom: 16px;"
                                }
                            }
                        },
                        {
                            dataField: "district",
                            label: { text: "District" },
                            editorType: "dxTagBox",
                            isRequired: false,
                            editorOptions: {
                                placeholder: "Select District(s)",
                                dataSource: areaOptions,
                                showSelectionControls: true,
                                showMultiTagOnly: false,
                                selectAllMode: "allPages",
                                width: "20vw"
                            }
                        }
                    ]
                });

            $("#proses").dxButton({
                text: "View",
                type: "default",

                useSubmitBehavior: true,
                onClick: function(e) {
                    let alm = "";
                    if (select == 0) {
                        alm = "{{ url('/rpt_get_salesPanelAll') }}";
                    } else {
                        alm = "{{ url('/rpt_get_salesPanelperMonth') }}";
                    }

                    var formInstance = $("#filterForm").dxForm("instance");
                    var formData = formInstance.option("formData");
                    var prd = new Date(formData.period);
                    var districts = formData.district;

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        url: alm,
                        data: {
                            "data": prd.toLocaleDateString(),
                            "district": districts
                        },
                        type: "post",
                        success: function(data) {
                            var dataGrid = $('#gridContainer').dxDataGrid('instance');
                            dataGrid.option('dataSource', data.data);
                            dataGrid.refresh();
                        },
                        error: function(xhr, status, response) {
                            errorHandlers(xhr, status);
                        }
                    });
                }
            });

            // Initialize the loading panel
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

            $("#exportByDistrict").dxButton({
              icon: 'fa fa-file-excel-o',
              text: "Export sales by district",
              type: "normal",
              onClick: async function(e) {
                const exportUrl = `${APP_BASE_URL}/report-customer-export`;
                const formInstance = $("#filterForm").dxForm("instance");
                const formData = formInstance.option("formData");
                const prd = new Date(formData.period);
                const districts = formData.district;
                const postData = {
                  period: prd.toISOString().slice(0, 10),
                  districts
                };
                // Show loading panel
                $("#exportLoadingPanel").dxLoadPanel("instance").option("visible", true);

                try {
                  const response = await fetch(exportUrl, {
                    method: "POST",
                    headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify(postData)
                  });

                  // Try to parse JSON if content-type is application/json (for error/no data)
                  const contentType = response.headers.get('content-type') || '';
                  if (contentType.includes('application/json')) {
                    const json = await response.json();
                    if (json && json.success === false) {
                      throw new Error(json.message || "No data found for the selected filters.");
                    }
                  }

                  if (!response.ok) {
                    let msg = 'Export failed.';
                    if ((response.headers.get('content-type') || '').includes('application/json')) {
                      msg = (await response.json()).message || msg;
                    }
                    throw new Error(msg);
                  }

                  const blob = await response.blob();
                  let filename = "Sales_Report_By_District.xlsx";
                  const disposition = response.headers.get('Content-Disposition');
                  if (disposition && disposition.includes('filename=')) {
                    filename = `${disposition.split('filename=')[1].replace(/['"]/g, '')}`;
                  }
                  const link = document.createElement('a');
                  const url = window.URL.createObjectURL(blob);
                  link.href = url;
                  link.download = filename;
                  document.body.appendChild(link);
                  link.click();
                  setTimeout(() => {
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                  }, 100);
                } catch (error) {
                  DevExpress.ui.notify({
                    message: error && error.message ? error.message : "Export failed.",
                    type: "error",
                    displayTime: 4000,
                    width: 450,
                    position: { my: "right top", at: "right top" }
                  });
                } finally {
                  $("#exportLoadingPanel").dxLoadPanel("instance").option("visible", false);
                }
              }
            });

            $("#gridContainer").dxDataGrid({
                dataSource: listData,
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
                    fileName: "Target Product",
                    allowExportSelectedData: true
                },
                showBorders: true,
                paging: {
                    enabled: true,
                    pageIndex: 0,
                    pageSize: 10
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
                    dataField: "distName",
                    caption: "District",
                    allowEditing: false
                  },
                  {
                    dataField: "areaName",
                    caption: "Area Name",
                    allowEditing: false
                  },
                  {
                    dataField: "empName",
                    caption: "Employee Name",
                    allowEditing: false
                  },
                  {
                    dataField: "oriBranchShortName",
                    caption: "Original Branch",
                    allowEditing: false
                  },
                  {
                    dataField: "branchShortName",
                    caption: "Branch",
                    allowEditing: false
                  },
                  {
                    dataField: "channelName",
                    caption: "Channel Name",
                    allowEditing: false
                  },
                  {
                    dataField: "fullDate",
                    caption: "Reference Date",
                    dataType: "date",
                    format: "shortDate",
                    allowEditing: false
                  },
                  {
                    dataField: "custNewCode",
                    caption: "Customer Code",
                    allowEditing: false
                  },
                  {
                    dataField: "custName",
                    caption: "Customer Name",
                    allowEditing: false
                  },
                  {
                    dataField: "prodGroup",
                    caption: "Product Group",
                    allowEditing: false
                  },
                  {
                    dataField: "prod_name",
                    caption: "Product Name",
                    allowEditing: false
                  },
                  {
                    caption: "CURRENT MONTH",
                    alignment: "center",
                    columns: [
                      {
                        dataField: "gross",
                        caption: "Gross",
                        dataType: "number",
                        format: "fixedPoint",
                        allowEditing: false
                      },
                      {
                        dataField: "qty",
                        caption: "Qty",
                        dataType: "number",
                        format: "fixedPoint",
                        allowEditing: false
                      },
                      {
                        dataField: "discount",
                        caption: "Discount",
                        dataType: "number",
                        format: "fixedPoint",
                        allowEditing: false
                      },
                      {
                        dataField: "netSales",
                        caption: "Nett",
                        dataType: "number",
                        format: "fixedPoint",
                        allowEditing: false
                      }
                    ]
                  },
                  {
                    caption: "LAST YEAR",
                    alignment: "center",
                    columns: [
                      {
                        dataField: "ly_gross",
                        caption: "LY Gross",
                        dataType: "number",
                        format: "fixedPoint",
                        allowEditing: false
                      },
                      {
                        dataField: "ly_qty",
                        caption: "LY Qty",
                        dataType: "number",
                        format: "fixedPoint",
                        allowEditing: false
                      },
                      {
                        dataField: "ly_discount",
                        caption: "LY Discount",
                        dataType: "number",
                        format: "fixedPoint",
                        allowEditing: false
                      },
                      {
                        dataField: "ly_netSales",
                        caption: "LY Nett",
                        dataType: "number",
                        format: "fixedPoint",
                        allowEditing: false
                      }
                    ]
                  }
                ],
                filterRow: {
                    visible: true,
                },
                summary: {

                  groupItems: [{
                      column: "netSales",
                      summaryType: "sum",
                      valueFormat: "fixedPoint",
                      //showInGroupFooter: false,
                      alignByColumn: true,
                      displayFormat: "{0}"
                  }
                  ],
                  totalItems: [{
                          column: "Category",
                          displayFormat: "Grand Total :"
                      },
                      {
                          column: "netSales",
                          summaryType: "sum",
                          valueFormat: "fixedPoint",
                          displayFormat: "{0}"
                      },
                  ]
                }

            });


        });
    </script>

@Stop
