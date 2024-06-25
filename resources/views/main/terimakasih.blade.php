@php
$setting = \App\Models\Setting::findorFail(1);

@endphp

<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keyword" content="">
    <meta name="author" content="theme_ocean">
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>{{ $setting->site_name }} - {{ $setting->site_slogan }}</title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('template/main') }}/images/logo.png" />
    <!--! END: Favicon-->
    <!--! BEGIN: Bootstrap CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('template/main') }}/css/bootstrap.min.css">
    <!--! END: Bootstrap CSS-->
    <!--! BEGIN: Vendors CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('template/main') }}/vendors/css/vendors.min.css">
    <!--! END: Vendors CSS-->
    <!--! BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('template/main') }}/css/theme.min.css">
    <!--! END: Custom CSS-->
    <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
    <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
    <!--[if lt IE 9]>
			<script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
    <style>
        .background-image {
                background: 
                    linear-gradient(
                    rgba(0, 0, 0, 0.7), 
                    rgba(0, 0, 0, 0.7)
                    ),
                    url("{{ asset('template/main/images/bg.jpg') }}");
                background-repeat: no-repeat;
                background-position: center center;
                background-attachment: fixed;
                background-size: cover;
                height: 80vh;
                width: 100%;
            }
    </style>
</head>

<body>
    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    <main class="auth-cover-wrapper">
        <div class="auth-cover-content-inner background-image">
            <div class="auth-cover-content-wrapper">
                <div class="auth-img">
                   
                </div>
            </div>
        </div>
        <div class="auth-cover-sidebar-inner" style="background: #2f467a;">
            <div class="auth-cover-card-wrapper">
                <div class="auth-cover-card p-sm-5">
                    <div class="wd-50 mb-5">
                        <img src="{{ asset('template/main') }}/images/logo.png" alt="" class="img-fluid">
                    </div>
                    <h2 class="fs-20 fw-bolder mb-4 text-white">Terima Kasih</h2>
                    <h4 class="fs-13 fw-bold mb-2 text-white">Kami telah mengirimkan pesan ke email <b>Anda</b> berisikan link untuk mengaktifkan akun Anda.</h4>
                    <p style="margin-top: 40px;" class="fs-12 fw-medium text-white">Mohon cek email Anda (folder inbox atau terkadang masuk ke folder spam),</span> dan klik link aktivasinya.</p>
                    
                        
                        <div class="mt-5">
                            <a href="{{ url('/') }}"><button type="submit" class="btn btn-lg btn-primary w-100">Terima Kasih</button></a>
                        </div>
                        
                    
                </div>
            </div>
        </div>
    </main>
  
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Vendors JS !-->
    <script src="{{ asset('template/main') }}/vendors/js/vendors.min.js"></script>
    <!-- vendors.min.js {always must need to be top} -->
    <!--! END: Vendors JS !-->
    <!--! BEGIN: Apps Init  !-->
    <script src="{{ asset('template/main') }}/js/common-init.min.js"></script>
    <!--! END: Apps Init !-->
    <!--! BEGIN: Theme Customizer  !-->
    <script src="{{ asset('template/main') }}/js/theme-customizer-init.min.js"></script>
    <!--! END: Theme Customizer !-->
    
</body>

</html>