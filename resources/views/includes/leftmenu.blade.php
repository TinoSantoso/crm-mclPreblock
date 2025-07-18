<aside  class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

      <!-- Sidebar user panel (optional) -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="{!! url('dist/img/user_male2-512.png') !!}" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          {{-- <p>{!! Auth::user()->username !!}</p> --}}
          <!-- Status -->
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>

      <!-- Sidebar Menu -->

                <ul class="sidebar-menu" data-widget="tree">

                        <li class="header">Main Navigation</li>
                        <li class="active treeview menu-open"><a href="{!! url('/dashboard') !!}"><i class="fa fa-home"></i> <span>Home</span></a></li>
                        <li class="treeview">
                        <a href="#">
                                <i class="fa fa-dashboard"></i> 
                                <span>Master</span> 
                                <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                                <li class="treeview" style="height: auto;">
                                        <a href="#"> 
                                                <i class="fa fa-users"></i>
                                                <span>User</span>
                                                <i class="fa fa-angle-left pull-right"></i>
                                        </a>
                                        <ul class="treeview-menu">
                                            <li><a href="{!! url('/user_group') !!}"> <i class="fa fa-group "></i>Group</a></li>
                                            <li><a href="{!! url('/group_prog') !!}"> <i class="fa fa-group "></i>Group Authorization</a></li>
                                            <li><a href="{!! url('/users') !!}"><i class="fa fa-user-plus"></i>Entry User</a></li>
                                        </ul>
                                </li>
                                <li class="treeview">
                                                        <a href="#"> <i class="fa  fa-map"></i><span>Area</span><i class="fa fa-angle-left pull-right"></i></a>
                                        <ul class="treeview-menu">
                                        <li><a href="{!! url('/region') !!}"><i class="fa fa-map-signs"></i>Region</a></li>
                                        <li><a href="{!! url('/area') !!}"><i class="fa fa-map-o"></i>Area</a></li>
                                        <li><a href="{!! url('/branch') !!}"><i class="fa  fa-map-pin"></i>Branch</a></li>
                                    </ul>
                                                    </li>
                                                    <li class="treeview">
                                                        <a href="#"> <i class="fa  fa-cart-arrow-down"></i><span>Product</span><i class="fa fa-angle-left pull-right"></i></a>
                                    <ul class="treeview-menu">
                                            <li class="treeview">
                                            <a href="#"> <i class="fa  fa-medkit"></i><span>GMD Medical</span><i class="fa fa-angle-left pull-right"></i></a>
                                            <ul class="treeview-menu">
                                                    <li><a href="{!! url('/GMD_Med_Cat') !!}"><i class="fa fa-stethoscope"></i>Category</a></li>
                                                    <li><a href="{!! url('/GMD_Med_Seg') !!}"><i class="fa fa-heartbeat"></i>Segment</a></li>
                                                    <li><a href="{!! url('/GMD_Med_ProdGroup') !!}"><i class="fa fa-ambulance"></i>Product Group</a></li>
                                                    <li><a href="{!! url('/GMD_Med_Brand') !!}"><i class="fa fa-user-md"></i>Brand</a></li>
                                                    <li><a href="{!! url('/GMD_Med_SubBrand') !!}"><i class="fa fa-h-square"></i>Sub-Brand</a></li>
                                            </ul>
                                            </li>
                                            <li><a href="{!! url('/prod_sku') !!}"><i class="fa fa-barcode"></i>SKU</a></li>
                                            <li><a href="{!! url('/prod_group') !!}"><i class="fa fa-cube"></i>Groups</a></li>
                                            <li><a href="{!! url('/prod_brand') !!}"><i class="fa fa-google-wallet"></i>Brand</a></li>
                                            <li><a href="{!! url('/prod_type') !!}"><i class="fa fa-tag"></i>Type</a></li>
                                            <li><a href="{!! url('/prod_line') !!}"><i class="fa fa-newspaper-o"></i>Line</a></li>
                                            <li><a href="{!! url('/prod_subline') !!}"><i class="fa fa-cubes"></i>Sub Line</a></li>
                                            <li><a href="{!! url('/prod_category') !!}"><i class="fa fa-cogs"></i>Category</a></li>
                                            <li><a href="{!! url('/prod_price') !!}"><i class="fa fa-eur"></i>Price</a></li>
                                    </ul>
                                                    </li>

                                <li class="treeview">
                                    <a href="#"> <i class="fa  fa-code-fork"></i><span>Channels</span><i class="fa fa-angle-left pull-right"></i></a>
                                    <ul class="treeview-menu">
                                        <li><a href="{!! url('/channel_group') !!}"><i class="fa  fa-share-alt"></i>Channel Group</a></li>
                                        <li><a href="{!! url('/channel') !!}"><i class="fa  fa-random"></i>Channel</a></li>
                                    </ul>
                                </li>
                                <li><a href="{!! url('/Distributor') !!}"><i class="fa fa-truck"></i>Distributor</a></li>
                                <li><a href="{!! url('/Customer') !!}"><i class="fa fa-users"></i>Customer</a></li>
                                <li class="treeview">
                                    <a href="#"> <i class="fa fa-user-plus"></i><span>Sales Rep.</span><i class="fa fa-angle-left pull-right"></i></a>
                                    <ul class="treeview-menu">
                                        <li><a href="{!! url('/position') !!}"><i class="fa  fa-star"></i>Position</a></li>
                                        <li><a href="{!! url('/PSemployee') !!}"><i class="fa  fa-user-secret"></i>Sales Employee</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#"> <i class="fa fa-tag"></i><span>MCL</span><i class="fa fa-angle-left pull-right"></i></a>
                                    <ul class="treeview-menu">
                                        <li><a href="{!! url('/mcl_area') !!}"><i class="fa fa-tint"></i>Area</a></li>
                                        <li><a href="{!! url('/mcl_department') !!}"><i class="fa fa-tint"></i>Department</a></li>
                                        <li><a href="{!! url('/mcl_level') !!}"><i class="fa fa-tint"></i>Level</a></li>
                                        <li><a href="{!! url('/mcl_role') !!}"><i class="fa fa-tint"></i>Role</a></li>
                                        <li><a href="{!! url('/mcl_specialty') !!}"><i class="fa fa-tint"></i>Specialty</a></li>
                                        <li><a href="{!! url('/mcl_unit') !!}"><i class="fa fa-tint"></i>Unit</a></li>
                                        <li><a href="{!! url('/mcl_category') !!}"><i class="fa fa-tint"></i>Category</a></li>
                                        <li><a href="{!! url('/mcl_fwd') !!}"><i class="fa fa-tint"></i>Fix Working Day</a></li>
                                        <li><a href="{!! url('/mcl_leave') !!}"><i class="fa fa-tint"></i>Employee Leave</a></li>
                                    </ul>
                                </li>
                                <li class="treeview">
                                    <a href="#"> <i class="fa fa-tag"></i><span>Micro Distributor</span><i class="fa fa-angle-left pull-right"></i></a>
                                    <ul class="treeview-menu">
                                        <li><a href="{!! url('/microDist') !!}"><i class="fa fa-tint"></i>Micro Distributor</a></li>
                                        <li><a href=""><i class="fa fa-tint"></i>Micro Dist Product</a></li>
                                        <li><a href="{!! url('/microDist_cust') !!}"><i class="fa fa-tint"></i>Micro Dist Customer</a></li>
                                        <li><a href=""><i class="fa fa-tint"></i>UOM</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                        <li class="treeview">
                            <a href="#"><i class="fa fa-file-text-o"></i> <span>Transaction</span> <i class="fa fa-angle-left pull-right"></i></a>
                            <ul class="treeview-menu">
                            <li class="treeview">
                                <a href="#"><i class="fa fa-child"></i> <span>PS</span> <i class="fa fa-angle-left pull-right"></i></a>
                                <ul class="treeview-menu">
                                    <li><a href="{!! url('/unmapping') !!}"> <i class="fa fa-circle"></i>PS Unmapping</a></li>
                                    <li><a href="{!! url('/uploadUnmapping') !!}"><i class="fa fa-circle"></i>Upload PS Unmapping</a></li>
                                    <li><a href="{!! url('/uploadUpdateMapping') !!}"><i class="fa fa-circle"></i>Upload Update PS Mapping</a></li>
                                    <li><a href="{!! url('/mapped') !!}"><i class="fa fa-circle"></i>PS Mapping</a></li>
                                    <li><a href="{!! url('/psMoved') !!}"><i class="fa fa-truck"></i>PS Moved</a></li>
                                    <li><a href="{!! url('/salesPanel') !!}"><i class="fa  fa-newspaper-o"></i>Sales Panel</a></li>
                                    <li><a href="{!! url('/uploadSalesPanel') !!}"><i class="fa  fa-newspaper-o"></i>Upload Sales Panel</a></li>
                                    
                                </ul>
                            </li>

                            <li class="treeview">
                                <a href="#"><i class="fa fa-calculator"></i> <span>Upload Target</span> <i class="fa fa-angle-left pull-right"></i></a>
                                <ul class="treeview-menu">
                                    <li><a href="{!! url('/target_product') !!}"><i class="fa fa-circle"></i>Target Product</a></li>
                                    <li><a href="{!! url('/target_area') !!}"> <i class="fa fa-circle"></i>Target Area</a></li>
                                    <li><a href="{!! url('/target_branch') !!}"> <i class="fa fa-circle"></i>Target Branch</a></li>
                                    <li><a href="{!! url('/target_channel') !!}"><i class="fa fa-circle"></i>Target Channel</a></li>
                                    <li><a href="{!! url('/target_ps') !!}"><i class="fa fa-circle"></i>Target PS</a></li>
                                </ul>
                            </li>

                            <li class="treeview">
                                <a href="#"><i class="fa fa-calculator"></i> <span>MCL</span> <i class="fa fa-angle-left pull-right"></i></a>
                                <ul class="treeview-menu">
                                    <li><a href="{!! url('/upload_fwd') !!}"><i class="fa fa-circle"></i>Upload FWD</a></li>
                                    <li><a href="{!! url('/mcl_new_contact') !!}"><i class="fa fa-circle"></i>Entry Contact</a></li>
                                    <li><a href="{!! url('/temp_CRM_SAP') !!}"><i class="fa fa-circle"></i>Entry CRM SAP</a></li>
                                    <li><a href="{!! url('/approval_temp_CRM_SAP') !!}"><i class="fa fa-circle"></i>Approval CRM SAP</a></li>
                                    <li><a href="{!! url('/transfer_CRM_SAP') !!}"><i class="fa fa-circle"></i>Transfer CRM SAP</a></li>
                                </ul>
                            </li>


                            <li class="treeview">
                                <a href="#"><i class="fa fa-calculator"></i> <span>Upload CRM</span> <i class="fa fa-angle-left pull-right"></i></a>
                                <ul class="treeview-menu">
                                    <li><a href="{!! url('/CRM_Account') !!}"><i class="fa fa-circle"></i>CRM Account</a></li>
                                    <li><a href="{!! url('/CRM_Contact') !!}"> <i class="fa fa-circle"></i>CRM Contact</a></li>
                                    <li><a href="{!! url('/CRM_ExternalID') !!}"> <i class="fa fa-circle"></i>CRM External ID</a></li>
                                    <li><a href="{!! url('/CRM_SAP') !!}"><i class="fa fa-circle"></i>CRM SAP</a></li>
                                    <li><a href="{!! url('/CRM_User') !!}"><i class="fa fa-circle"></i>CRM USER</a></li>
                                    <li><a href="{!! url('/CRM_userTarget') !!}"><i class="fa fa-circle"></i>CRM USER Target</a></li>
                                    <li><a href="{!! url('/CRM_Visit') !!}"><i class="fa fa-circle"></i>CRM Visit</a></li>
                                    <li><a href="{!! url('/CRM_ProductDetailing') !!}"><i class="fa fa-circle"></i>CRM Product Detailing</a></li>
                                    <li><a href="{!! url('/CRM_Opportunities') !!}"><i class="fa fa-circle"></i>CRM Opportunities</a></li>
                                    <li><a href="{!! url('/CRM_OpportunitiesProd') !!}"><i class="fa fa-circle"></i>CRM Opportunities Prod.</a></li>
                                </ul>
                            </li>
                                <li class="treeview">
                                <a href="#"><i class="fa fa-truck"></i> <span>Logistic</span> <i class="fa fa-angle-left pull-right"></i></a>
                                <ul class="treeview-menu">
                                    <li><a href="{!! url('/sell_in_planact') !!}"><i class="fa fa-circle"></i>Upload Sell In Plan & Act</a></li>
                                    <li><a href="{!! url('/forecast_accuracy_sellin') !!}"><i class="fa fa-circle"></i>Upload Forecast Acc. Sell in</a></li>
                                    <li><a href="{!! url('/forecast_accuracy_sellout') !!}"><i class="fa fa-circle"></i>Upload Forecast Acc. Sell out</a></li>
                                </ul>
                            </li>
                                <li class="treeview">
                                <a href="#"><i class="fa fa-ship"></i> <span>Sirclo</span> <i class="fa fa-angle-left pull-right"></i></a>
                                <ul class="treeview-menu">
                                    <li><a href="{!! url('/upload_SalesCirclo') !!}"><i class="fa fa-users"></i>Upload Sales Sirclo</a></li>
                                                    <li><a href="{!! url('/upload_StokSirclo') !!}"><i class="fa fa-users"></i>Upload Stock Sirclo</a></li>
                                </ul>
                            </li>
                                <li class="treeview">
                                <a href="#"><i class="fa fa-hospital-o"></i> <span>APL</span> <i class="fa fa-angle-left pull-right"></i></a>
                                <ul class="treeview-menu">
                                    <li><a href="{!! url('/upload_SalesAPL') !!}"><i class="fa fa-cloud-upload"></i>Upload Sales APL</a></li>
                                                    <li><a href="{!! url('/upload_StockAPL') !!}"><i class="fa fa-upload"></i>Upload Stock APL</a></li>
                                                        <li><a href="{!! url('/uploadMapBranchAPL') !!}"><i class="fa fa-upload"></i>Upload Map Branch APL</a></li>
                                </ul>
                            </li>
                            <li class="treeview">
                                <a href="#"><i class="fa fa-hospital-o"></i> <span>AAM</span> <i class="fa fa-angle-left pull-right"></i></a>
                                <ul class="treeview-menu">
                                    <li><a href="{!! url('/upload_AAM') !!}"><i class="fa fa-cloud-upload"></i>Upload AAM</a></li>
                                                    </ul>
                            </li>
                            <li class="treeview">
                                <a href="#"><i class="fa fa-bullseye"></i> <span>Sales Focus Hospital</span> <i class="fa fa-angle-left pull-right"></i></a>
                                <ul class="treeview-menu">
                                    <li><a href="{!! url('/upload_SalesFocusHospital') !!}"><i class="fa  fa-hospital-o"></i>Upload Sales Focus Hospital</a></li>
                                    <li><a href="{!! url('/SalesFocusHospital') !!}"><i class="fa fa-th-list"></i>List Sales Focus Hospital</a></li>
                                </ul>
                            </li>
                            <li><a href="{!! url('/upload_prodPrice') !!}"><i class="fa fa-cloud-upload"></i>Upload Master Product Price</a></li>
                            <li><a href="{!! url('/reload_essity_league') !!}"><i class="fa  fa-repeat"></i>Reload Essity League</a></li>
                            <li><a href="{!! url('/mirroring_PS') !!}"><i class="fa fa-users"></i>Mirroring PS</a></li>
                            <li><a href="{!! url('/uploadTinsentif_govNonGov') !!}"><i class="fa fa-users"></i>Upload Insentif Goveerment & non Goverment</a></li>
								
                </ul>
                             </li>
                             <li class="treeview">
                                  <a href="#"><i class="fa fa-line-chart"></i> <span>Report</span> <i class="fa fa-angle-left pull-right"></i></a>
                                  <ul class="treeview-menu">
                                    <li class="treeview">
                                        <a href="#"><i class="fa fa-calculator"></i> <span>Target</span> <i class="fa fa-angle-left pull-right"></i></a>
                                        <ul class="treeview-menu">
                                         <li><a href="{!! url('/rpt_targetProduct') !!}"><i class="fa fa-circle"></i>Target Product</a></li>
                                         <li><a href="{!! url('/rpt_targetPS') !!}"><i class="fa fa-circle"></i>Target PS</a></li>
                                         <li><a href="{!! url('/rpt_targetChannel') !!}"><i class="fa fa-circle"></i>Target Channel</a></li>
										                     <li><a href="{!! url('/rpt_targetBranch') !!}"><i class="fa fa-circle"></i>Target Branch</a></li>
										                     <li><a href="{!! url('/rpt_targetArea') !!}"><i class="fa fa-circle"></i>Target Area</a></li>
                                       </ul>
                                    </li>
                                    <li class="treeview">
                                         <a href="#"><i class="fa fa-calculator"></i> <span>Master</span> <i class="fa fa-angle-left pull-right"></i></a>
                                         <ul class="treeview-menu">
										   <li><a href="{!! url('/rpt_masterDistributor') !!}"><i class="fa fa-circle"></i>Distributor</a></li>
                                           <li><a href="{!! url('/rpt_masterProduct') !!}"><i class="fa fa-circle"></i>Product</a></li>
                                           <li><a href="{!! url('/rpt_masterCustomer') !!}"><i class="fa fa-circle"></i>Customer</a></li>
                                           <li><a href="{!! url('/rpt_masterPS') !!}"><i class="fa fa-circle"></i>PS</a></li>
                                           <li><a href="{!! url('/rpt_newChannel') !!}"><i class="fa fa-circle"></i>New Channel</a></li>
                                           <li><a href="{!! url('/rpt_newBranch') !!}"><i class="fa fa-circle"></i>New Branch</a></li>
                                           <li><a href="{!! url('/rpt_newPS') !!}"><i class="fa fa-circle"></i>New PS</a></li>
										   <li><a href="{!! url('/rpt_unregisterPS') !!}"><i class="fa fa-circle"></i>Unregister PS</a></li>
					                       <li><a href="{!! url('/rpt_productPrice') !!}"><i class="fa fa-circle"></i>Product Price</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="{!! url('/rpt_unmapping') !!}"><i class="fa fa-circle"></i>Customer Unmapping</a></li>
                                    <li><a href="{!! url('/rpt_mapping') !!}"><i class="fa fa-circle"></i>Customer Mapping</a></li>
									<li><a href="{!! url('/rpt_hospitalFocus') !!}"><i class="fa fa-circle"></i>Hospital Focus</a></li>
								    <li><a href="{!! url('/rpt_salesPanel') !!}"><i class="fa fa-circle"></i>Sales Panel</a></li>
								    <li class="treeview">
                                          <a href="#"><i class="fa fa-calculator"></i> <span>Logistic</span> <i class="fa fa-angle-left pull-right"></i></a>
                                          <ul class="treeview-menu">
                                              <li><a href="{!! url('/rpt_sell_in_planact') !!}"><i class="fa fa-circle"></i>Sell In Plan & Actual</a></li>
                                              <li><a href="{!! url('/rpt_forecast_acc_sellin') !!}"><i class="fa fa-circle"></i>Forecast Accuracy Sell In</a></li>
                                              <li><a href="{!! url('/rpt_forecast_acc_sellout_branch') !!}"><i class="fa fa-circle"></i>Forecast Acc. Sell Out Branch</a></li>
                                              <li><a href="{!! url('/rpt_forecast_acc_sellout_national') !!}"><i class="fa fa-circle"></i>Forecast Acc. Sell Out National</a></li>
                                          </ul>
                                    </li>
									  <!--<li><a href="{!! url('/unAuthenticatedUser') !!}"><i class="fa fa-circle"></i>Session Timeout</a></li>-->
                                  </ul>
                            </li>
                            <li class="treeview">
                                <a href="#"><i class="fa fa-th-large"></i> <span>Preblock MCL</span> <i class="fa fa-angle-left pull-right"></i></a>
                                <ul class="treeview-menu">
                                    <li><a href="{!! url('/preblock') !!}"><i class="fa fa-edit"></i>Entry Preblock MCL</a></li>
                                </ul>
                            </li>

                            <!-- Logout Button -->
                            <li>
                                <a href="#" onclick="handleLogout(event)">
                                    <i class="fa fa-sign-out"></i> <span>Logout</span>
                                </a>
                            </li>

                            <script>
                            function handleLogout(e) {
                                e.preventDefault();
                                localStorage.removeItem('jwt_token');
                                sessionStorage.removeItem('jwt_token');

                                fetch(`${APP_BASE_URL}/api/auth/logout`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    }
                                })
                                .then(() => {
                                    window.location.href = '/login';
                                })
                                .catch(error => {
                                    console.error('Error during logout:', error);
                                });
                            }
                            </script>

                  </ul>

      <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
  </aside >
