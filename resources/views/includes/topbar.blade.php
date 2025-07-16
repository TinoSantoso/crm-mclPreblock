  <header class="main-header">
 
  
    {{-- @if(count($errors)>0)
        @foreach ($errors->all() as $error)
            alert('{{ $error }}');
        @endforeach
    @endif --}}

  

    <!-- Logo -->
    <a href="{!! url('/home') !!}" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      {{-- <span class="logo-mini">
          <img style="position:absolute; TOP:10px; LEFT:10px; WIDTH:30px; HEIGHT:30px"alt="Essity Indonesia" src="{!! URL::to('dist/img/favicon.png') !!}" class="hidden-xs" /> 
	  </span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg" >
          <img style="position:absolute; TOP:7px; LEFT:60px; WIDTH:120px; HEIGHT:40px"alt="Essity Indonesia" src="{!! URL::to('dist/img/essity.png') !!}" class="hidden-xs" /> 
      </span> --}}
      
      
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>
      <!-- Navbar Right Menu -->
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">                  
			  <!-- User Account Menu -->
			<li class="dropdown user user-menu">
				<!-- Menu Toggle Button -->
				{{-- <a href="#" class="dropdown-toggle" data-toggle="dropdown">
				  <!-- The user image in the navbar-->
				  <img src="{!! url('dist/img/user_male2-512.png') !!}" class="user-image" alt="User Image">
				  <!-- hidden-xs hides the username on small devices so only the image appears. -->
				  <span class="hidden-xs"><?php if(!is_null(Auth::user())) { echo Auth::user()->name; } ?></span>
				</a>
				<ul class="dropdown-menu">
				  <!-- The user image in the menu -->
				  <li class="user-header">
					<img src="{!! url('dist/img/user_male2-512.png') !!}" class="img-circle" alt="User Image">

					<p>
					  <?php if(!is_null(Auth::user())) { echo Auth::user()->name; } ?>
					
					</p>
				  </li>
				  
				  <!-- Menu Footer-->
				  <li class="user-footer">
					<div class="pull-left">
					  <a href="#" class="btn btn-default btn-flat chpass">Change Password</a>
					</div>
					<div class="pull-right">
					  <a href="{!! url('/logout') !!}" class="btn btn-default btn-flat">Sign out</a>
					</div>
				  </li>
				</ul> --}}
			</li>         
        </ul>
      </div>
    </nav>
  </header>
  {{-- <script>
  $(document).ready(function() {
  $('.chpass').click(function() 
  {
	  
  bootbox.dialog({
                title: "Change Password",
                message: '<div class="row">  ' +
                    '<div class="col-md-6"> ' +
                   
					'<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
					'<div class="form-group"> ' +
					'<label for="oldpass">Old Password:</label> ' +
					'<input type="password" class="form-control" id="oldpass" name="oldpass" placeholder="Enter Old Password"> ' +
					'<span class="help-inline"></span> </div>' +
					'<div class="form-group"> ' +
					'<label for="newpwd">New Password:</label> ' +
					'<input type="password" class="form-control" id="newpwd" name="newpwd" placeholder="Enter new password"> ' +
					'<span class="help-inline"></span></div> ' +
					'<div class="form-group"> ' +
					'<label for="conpwd">Confirm Password:</label> ' +
					'<input type="password" class="form-control" id="conpwd" name="conpwd" placeholder="Enter confirm password"> ' +
					'<span class="help-inline"></span></div> ' +
					' </div>  </div>',
					 buttons: {
                    success: {
                        label: "Save",
                        className: "btn-success",
                        callback: function () 
						{
							   $.ajaxSetup({
                                                headers:
                                                {
                                                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                                                }
                                            });
							   $.ajax({
                                           url: "{!! url("/change_pass") !!}",
                                          data: {
                                                     "oldpass" : $('#oldpass').val(),
                    								 "newpass" : $('#newpwd').val(),
                    								 "confirmed" : $('#conpwd').val()
                                                },
                                          type:"post",
                                          success: function( data ) 
                                          {
    								          bootbox.alert({
                                                                      title: data.status,
                                                                    message: data.msg
                                                                 });
								         },
										 error: function(response)
                                          {
                                                  var err='';
                                                  var err1='';
                                                  var err2='';
                                                  var err3='';
                                                  
                                                  var i=0;
                                                  if (response.responseJSON.errors.hasOwnProperty("confirmed")){
                                                       i=i+1;
                                                       err1=i+'. '+response.responseJSON.errors.confirmed;
                                                    }
                                                    if (response.responseJSON.errors.hasOwnProperty("newpass")){
                                                      i=i+1;
                                                       err2=i+'. '+response.responseJSON.errors.newpass;
                                                    }
                                                    if (response.responseJSON.errors.hasOwnProperty("oldpass")){
                                                      i=i+1;
                                                       err3=i+'. '+response.responseJSON.errors.oldpass;
                                                    }
                                                     err=err1 +'<br />' + err2 +'<br />'+ err3;
                                                    
                                             
                                                   
                                                   message('error', err)
                                               
                                                                                                                   
                                          }

							    
                                    });
                          } //---call back---
                     }  //---success---
                }  ///---button--
          
            });//---bootbox dialog--
      });
});
  
  
  
  
  </script> --}}