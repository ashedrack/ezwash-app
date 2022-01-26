<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="expires" content="0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="Ezwashndry is a leading chain of laundromats with stores all across Lagos, offering customers the choice of either service or drop off. We offer a modern, comfortable “third space” where people can sit and relax while they wait.">
    <meta name="keywords" content="">
    <meta name="author" content="INITS Limited(http://initsng.com)">
    <title>Ezwashndry Laundromat | A modern chain of laundromats in Lagos, Nigeria</title>
    <link rel="apple-touch-icon" href="{{ asset('/images/ezwash_logo.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/appicon.png') }}">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Quicksand:300,400,500,700"
          rel="stylesheet">
    <link href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css" rel="stylesheet">

    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('theme_assets/css/vendors.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('theme_assets/plugins/loaders/loader.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('theme_assets/vendors/extensions/sweetalert/sweetalert.css') }}">

    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <!-- END Custom CSS-->

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-FFW8MNK2XR"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-FFW8MNK2XR');
    </script>
</head>
<body class="horizontal-layout horizontal-menu horizontal-menu-padding 1-column  bg-full-screen-image menu-expanded blank-page blank-page"
      data-open="click" data-menu="horizontal-menu" data-col="1-column">
<div class="preloader" style="display: block;">
    <div class="md-content">
        <div id="spinner-holder">
            <div style="height: 50px;text-align: center;">
                <img src="{{ asset('/images/ezwash_logo.png') }}" alt="Ezwash Logo" height="50px" style="margin: 0 auto;">
            </div>
            <div class="text-center">
                <p>Ezwash CRM</p>
            </div>
            <div class="loader-wrapper">
                <div class="loader-container">
                    <div class="ball-pulse loader-primary">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="app-content center-layout auth_wrapper">
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <section class="flexbox-container">
                <div class="col-12 d-flex align-items-center justify-content-center">
                    <div class="col-md-4 col-10 box-shadow-2 p-0">
                        <div class="card border-grey border-lighten-3 px-1 py-1 m-0">
                            <div class="card-header border-0">
                                <div class="card-title text-center">
                                    <img src="{{ asset('/images/ezwash_logo.png') }}" width="100px" alt="Ezwash Logo">
                                </div>
                                @hasSection('instruction')
                                <h6 class="card-subtitle line-on-side text-muted text-center font-small-3 pt-2">
                                    <span>@yield('instruction')</span>
                                </h6>
                                @endif
                                @if(session('status'))
                                    <div class="alert bg-primary">
                                        <p>{{ session('status') }}</p>
                                    </div>
                                @endif
                                @if ($errors->any())
                                    <div class="alert bg-danger">
                                        <ul class="display-inline-block">
                                            @foreach ($errors->all() as $error)
                                                <li class="text-white">{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            @yield('content')
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<footer class="footer footer-transparent footer-light navbar-shadow">
    @component('components/footer-copyright')
    @endcomponent
</footer>
<script src="{{ asset('theme_assets/js/vendors.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('theme_assets/vendors/extensions/sweetalert/sweetalert.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/sitewide_script.js') }}" type="text/javascript"></script>

@yield('more-scripts')

<script>
    $(document).ready(function(){
        $('.preloader').hide();
        $('.reset-password-form').on('submit', function(){
            if($('.user_password').val().length < 8){

                $('#error_message').html('Password must be at least 8 characters').css('text-align', 'center').css('color', '#FF9494');
                return false;

            }else if($('.user_password').val() !== $('#password_confirmation').val()){

                $('#error_message').html('The two passwords don\'t match!').css('text-align', 'center').css('color', '#FF9494');
                return false;

            }else{
                console.log("Form Validated");
                return true;
            }
        });
    });
</script>
</body>
</html>
