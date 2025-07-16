<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sales Data Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Web Application E-Expenses Essity Indonesia">
    <meta name="author" content="Dendy Insan Nugraha">
    {{-- <meta id="#token" name="csrf-token" content="{!! csrf_token() !!}" /> --}}
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="{!! url('asset/css/bootstrap.min.css') !!}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{!! url('asset/css/font-awesome.min.css') !!}">
    
    <!-- Ionicons -->
    <link rel="stylesheet" href="{!! url('asset/css/ionicons.min.css') !!}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{!! url('dist/css/AdminLTE.css') !!}">
    <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
            page. However, you can choose any other skin. Make sure you
            apply the skin class to the body tag so the changes take effect.
    -->
    <link rel="stylesheet" href="{!! url('dist/css/skins/skin-black.min.css') !!}">
        <link rel="shortcut icon" href="{!! url('dist/img/favicon.png') !!}">
        <!-- jQuery 2.2.0 -->


    <link href="{!! url('asset/css/devexp/dx.common.css') !!}" rel='stylesheet'>
    {{-- <link href="{!! url('asset/css/devexp/dx.light.css') !!}" rel='stylesheet'> --}}
    {{-- <script type="text/javascript" src="{!! url('asset/js/devexp/jquery.min.js')!!}"></script> --}}

    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    
    <!-- DevExpress Theme -->
    <link rel="stylesheet" href="https://cdn3.devexpress.com/jslib/24.1.7/css/dx.light.css">
    
    <!-- DevExpress Core -->
    <script type="text/javascript" src="https://cdn3.devexpress.com/jslib/24.1.7/js/dx.all.js"></script>
    <script type="text/javascript">
        const APP_BASE_URL = {!! json_encode(url('/')) !!};
        const APP_HOST = window.location.host;
        const APP_ORIGIN = window.location.origin;
        
        // Check for JWT token and ensure it's in sessionStorage
        document.addEventListener('DOMContentLoaded', async function() {
            const token = localStorage.getItem('jwt_token') || sessionStorage.getItem('jwt_token');
            if (!token) {
                // If no token, redirect to login page
                window.location.href = '/login';
                return;
            }
            
            // Ensure token is in sessionStorage
            sessionStorage.setItem('jwt_token', token);
            
            // Send token to server to store in session if not already done
            try {
                await fetch('/api/auth/store-token', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({ token: token })
                });
            } catch (error) {
                console.error('Error storing token in session:', error);
            }
        });
    </script>

</head>

<body class="hold-transition skin-black sidebar-collapse sidebar-mini">
    <div class="wrapper">
        <!-- Main Header -->
        @include("includes.topbar")
        <!-- Left side column. contains the logo and sidebar -->
        @include("includes.leftmenu")

        <div class="content-wrapper">
            @yield('content')
        </div>
        <!-- Main Footer -->
        <footer class="main-footer">
                @include("includes.footer")		
        </footer>
    </div>

    <!-- Bootstrap 3.3.6 -->
    <script src="{!! url('asset/js/bootstrap.min.js') !!}"></script>
    <!-- AdminLTE App -->
    <script src="{!! url('dist/js/app.js') !!}"></script>

    <script src="{!! url('asset/js/fastclick/fastclick.js') !!}"></script>
    <script src ="{!! url('asset/js/bootbox.min.js') !!}"></script>
    <script src="{!! url('asset/js/moment.min.js') !!}"></script>
    <script type="text/javascript" language="javascript" src="{!! url('asset/js/numeral.min.js')!!}"></script>
    <script type="text/javascript" language="javascript" src="{!! url('asset/js/devexp/jszip.js')!!}"></script>
    <script type="text/javascript" language="javascript" src="{!! url('asset/js/devexp/devextreme-license.js')!!}"></script>
    <script type="text/javascript" language="javascript" src="{!! url('asset/js/devexp/cldr.js')!!}"></script>   
    <script type="text/javascript" language="javascript" src="{!! url('asset/js/devexp/cldr/event.js')!!}"></script>
    <script type="text/javascript" language="javascript" src="{!! url('asset/js/devexp/cldr/supplemental.js')!!}"></script>
    <script type="text/javascript" language="javascript" src="{!! url('asset/js/devexp/globalize.js')!!}"></script>
    <script type="text/javascript" language="javascript" src="{!! url('asset/js/devexp/globalize/message.js')!!}"></script>
    <script type="text/javascript" language="javascript" src="{!! url('asset/js/devexp/globalize/number.js')!!}"></script>
    <script type="text/javascript" language="javascript" src="{!! url('asset/js/devexp/globalize/currency.js')!!}"></script>
    <script type="text/javascript" language="javascript" src="{!! url('asset/js/devexp/globalize/date.js')!!}"></script>
    <script type="text/javascript" src="{!! url('asset/js/tools.js')!!}"></script>
    <script type="text/javascript" language="javascript" src="{!! url('asset/js/bootstrap-filestyle.js')!!}"></script>
</body>
</html>