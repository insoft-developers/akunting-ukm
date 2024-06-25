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
    <style>
       
        .background-image {
            background: 
                linear-gradient(
                rgba(0, 0, 0, 0.7), 
                rgba(0, 0, 0, 0.7)
                ),
                url("{{ asset('template/main/images/bg_login.jpg') }}");
            background-repeat: no-repeat;
            background-position: center center;
            background-attachment: fixed;
            background-size: cover;
            height: 100vh;
            width: 100%;
        }
        .img-login{
            width: 150px;
            height: 150px;
            object-fit: contain;
            margin-top: 53px;
            margin-bottom: -41px;
        }
        .card-login{
            opacity: 0.9;
            margin-left: 30px !important;
            margin-right: 30px !important;
        }
        .help-block{
            color: red;
            margin-top: 10px;
        }
    </style>


</head>

<body class="background-image">
    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    <main class="auth-minimal-wrapper">
        <div class="auth-minimal-inner">
            <div class="minimal-card-wrapper">
                <center><img class="img-login" src="{{ asset('template/main/images/login_depan.png') }}"></center>
                <div class="card mb-4 mt-5 mx-4 mx-sm-0 position-relative card-login">
                   
                    <div class="card-body p-sm-5">
                        @if ( session()->has('error') )
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="alert alert-danger">
                                        {!! session('error') !!}
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ( session()->has('success') )
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="alert alert-success">
                                        {!! session('success') !!}
                                    </div>
                                </div>
                            </div>
                        @endif


                        <form method="POST" action="{{ route('login.action') }}" class="w-100 pt-2">
                            @csrf
                            <div class="mb-4">
                                <input type="email" class="form-control" placeholder="Alamat Email" name="email">
                                @if($errors->has('email'))
                                    <span class="help-block">{{ $errors->first('email') }}</span>
                                @endif
                            </div>
                            <div class="mb-3">
                                <input type="password" class="form-control" placeholder="Kata Sandi" name="password">
                                @if($errors->has('password'))
                                <span class="help-block">{{ $errors->first('password') }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="rememberMe">
                                        <label class="custom-control-label c-pointer" for="rememberMe">Remember Me</label>
                                    </div>
                                </div>
                                <div>
                                    <a href="auth-reset-minimal.html" class="fs-11 text-primary">Lupa kata sandi?</a>
                                </div>
                            </div>
                            <div class="mt-5">
                                <button type="submit" class="btn btn-lg btn-primary w-100">Masuk</button>
                            </div>
                        </form>
                     
                        <div class="mt-5 text-muted">
                            <span> Belum punya akun?</span>
                            <a href="{{ url('frontend_register') }}" class="fw-bold">Buat Akun</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="margin-top: 100px;"></div>
    </main>
    
   
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