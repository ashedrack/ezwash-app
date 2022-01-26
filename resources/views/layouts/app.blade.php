<?php
    $asset_version = rand(1, 9999999);
    $authUser = Auth::user();
?>
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
    <link rel="stylesheet" type="text/css" href="{{ asset('theme_assets/vendors/extensions/sweetalert/sweetalert.css') }}">
    <!-- END VENDOR CSS-->

    <link href="{{ asset('plugins/jquery-ui/jquery-ui.min.css') }}" rel="stylesheet">
    <!-- BEGIN MODERN CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}?v={{$asset_version}}">
    <!-- END MODERN CSS-->

    <link rel="stylesheet" type="text/css" href="{{ asset('theme_assets/css/menu/horizontal-menu.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('theme_assets/colors/palette-gradient.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('theme_assets/fonts/simple-line-icons/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('theme_assets/plugins/loaders/loader.min.css')}}">

    <link rel="stylesheet" type="text/css" href="{{ asset('theme_assets/vendors/css/datatable/dataTables.bootstrap4.min.css') }}">

    <!-- BEGIN Page Level CSS-->
    @yield('page-specific-styles')

    <!-- BEGIN Custom CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}?v={{$asset_version}}">
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
<body class="horizontal-layout horizontal-menu horizontal-menu-padding 2-columns   menu-expanded"
      data-open="click" data-menu="horizontal-menu" data-col="2-columns">
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
<div id="data-loader-wrapper" tabindex="-1" style="display: none">
    <div class="data-loading">
        <div class="md-modal md-modal-mini md-effect-11 inner_loading md-show" id="modal-11">
            <div class="md-content">
                <div class="spinner-holder ">
                    <div class="text-center">
                        <p id="loading-message"></p>
                    </div>
                    <div class="loader-wrapper">
                        <div  class="loader-container">
                            <div class="ball-pulse loader-danger">
                                <div class="bounce1"></div>
                                <div class="bounce2"></div>
                                <div class="bounce3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Start In app Notification popup -->
<div class="card" id="popup-notice" style="display: none;">
    <div id="card-content">
        <div class="card-body">
            <h4 id="popup-notice-title">Notice title</h4>
            <p id="popup-notice-message">The message</p>
        </div>
    </div>
</div>

<!-- End In app Notification popup -->
<nav class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-without-dd-arrow navbar-static-top navbar-light navbar-brand-center">
    <div class="navbar-wrapper">
        <div class="navbar-header">
            <ul class="nav navbar-nav flex-row">
                <li class="nav-item mobile-menu d-md-none mr-auto"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ft-menu font-large-1"></i></a></li>
                <li class="nav-item">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <img class="brand-logo" alt="Ezwash Logo" src="{{ asset('/images/ezwash_logo.png') }}">
                    </a>
                </li>
                <li class="nav-item d-md-none">
                    <a class="nav-link open-navbar-container" data-toggle="collapse" data-target="#navbar-mobile"><i class="la la-ellipsis-v"></i></a>
                </li>
            </ul>
        </div>
        <div class="navbar-container container center-layout">
            <div class="collapse navbar-collapse" id="navbar-mobile">
                <ul class="nav navbar-nav mr-auto float-left">
                    <li class="nav-item nav-search">
                        <form id="sitewide-search-form">
                        <div class="search-input open">
                            <input id="gen_search_token" type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input class="input" id="gen_search_string" type="text" placeholder="Search Customer by username or email...">
                        </div>
                        <button class="btn btn-primary ml-1" id="show_filter_section" type="submit">Search</button>
                        </form>
                    </li>
                </ul>
                <ul class="nav navbar-nav float-right">
                    <li class="dropdown dropdown-user nav-item">
                        <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">
                <span class="mr-1">Hello,
                  <span class="user-name text-bold-700">{{ $authUser->name }}</span>
                </span>
                            <span class="avatar avatar-online">
                  <img src="{{ asset('images/default_employee_avatar.png') }}" alt="avatar"><i></i></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="ft-user"></i>Profile</a>
                            <div class="dropdown-divider"></div><a class="dropdown-item" href="#" onclick="$('#logoutForm').submit();"><i class="ft-power"></i> Logout</a>
                            <form id="logoutForm" action="{{ route('logout') }}" method="post" style="display:none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                    <?php
                    $unredNotifications = $authUser->notifications()->where('status', App\Models\Notification::UNREAD)->orderBy('created_at', 'DESC')->get();
                    ?>
                    <li class="dropdown dropdown-notification nav-item">
                        <a class="nav-link nav-link-label" href="#" data-toggle="dropdown">
                            <i class="ficon ft-bell"></i>
                            @if(count($unredNotifications) > 0)
                                <span class="badge badge-pill badge-default badge-danger badge-default badge-up badge-glow">{{sizeof($unredNotifications)}}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                            <li class="dropdown-menu-header">
                                <h6 class="dropdown-header m-0">
                                    <span class="grey darken-2">Notifications</span>
                                </h6>
                                @if(count($unredNotifications) > 0)
                                    <span class="notification-tag badge badge-default badge-danger float-right m-0">{{count($unredNotifications)}} New</span>
                                @endif
                            </li>
                            <li class="scrollable-container media-list w-100">

                                @if(count($unredNotifications) > 0)
                                    @foreach($unredNotifications as $unreadNotification)
                                    <a href="@if(!empty($unreadNotification->url)) {{$unreadNotification->url}} @else javascript:void(0) @endif" data-markread-url="{{ route('notification.mark_as_read', ['notification' => $unreadNotification->id ]) }}" class="notification-item">
                                        <div class="media">
                                            <div class="media-left align-self-center"><i class="ft-check-circle icon-bg-circle bg-cyan"></i></div>
                                            <div class="media-body">
                                                <h6 class="media-heading">{{$unreadNotification->heading}}</h6>
                                                <p class="notification-text font-small-3 text-muted">{{$unreadNotification->message}}</p>
                                                <small>
                                                    <time class="media-meta text-muted">{{$unreadNotification->created_at->diffForHumans()}}</time>
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                    @endforeach
                                @else
                                    <div class="container">
                                        <br>
                                        <p class="notification-text font-small-4 text-info">No new notification at the moment!</p>
                                        <br>
                                    </div>

                                @endif

                            </li>
                            <li id="readAllBtn" class="notification-items dropdown-menu-footer"><a class="dropdown-item text-muted text-center" href="{{ route('notification.list') }}">Read all notifications</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

@component('components.navigation', compact('authUser'));
@endcomponent

<div class="app-content container center-layout mt-2 ">
    @yield('content')
</div>

@component('components.customers_search_result')
@endcomponent

<footer class="footer footer-transparent footer-light navbar-shadow">
    @component('components/footer-copyright')
    @endcomponent
</footer>

<!-- BEGIN VENDOR JS-->
<script src="{{ asset('theme_assets/js/vendors.min.js') }}" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script src="{{ asset('plugins/jquery-validation/core.js') }}" type="text/javascript"></script>
<script src="{{ asset('theme_assets/vendors/extensions/sweetalert/sweetalert.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('theme_assets/vendors/js/datatable/datatables.min.js') }}"></script>
<!-- END VENDOR JS-->

<!-- BEGIN PAGE VENDOR JS-->
<script type="text/javascript" src="{{ asset('theme_assets/vendors/js/ui/jquery.sticky.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- END PAGE VENDOR JS-->

<!-- BEGIN MODERN JS-->
<script src="{{ asset('theme_assets/js/app-menu.js') }}" type="text/javascript"></script>
<script src="{{ asset('theme_assets/js/app.js') }}" type="text/javascript"></script>
<script src="{{ asset('theme_assets/js/scripts/customizer.js') }}" type="text/javascript "></script>
<script src="{{ asset('plugins/lodash.min.js') }}" type="text/javascript"></script>
<!-- END MODERN JS-->

<!-- BEGIN PAGE LEVEL JS-->
<script src="{{ asset('js/sitewide_script.js') }}?v={{$asset_version}}" type="text/javascript"></script>
<script>

    (function($){
        @if(session('status'))
            swal("{{session('title')}}", "{{session('message')}}", "{{session('status')}}");
        @endif

        const token = "{{ csrf_token() }}";
        $(document).on('click', '.notification-item', function(event){
            const markReadUrl = $(this).data('markread-url');
            readNotification(markReadUrl);
        });
        $(document).on('click', '.notification-items', function(event){
            readAllNotifications();
        });

        function readAllNotifications()
        {
            $.ajax({
                method: 'POST',
                url: "{{url('notifications/read_all')}}",
                data: { "_token": token }
            }).done(function(response){
                console.log(response);
            }).fail(function(response){
                console.log(response);
            });

        }
        function readNotification(url)
        {
            request = $.ajax({
                method: 'GET',
                url,
                data: { "_token": token }
            }).done(function(response){
                return response['status'];
            }).fail(function(error){
                console.error(error);
                return false;
            });
        }
    }(jQuery));

</script>
@yield('more-scripts')
<!-- END PAGE LEVEL JS-->
</body>
</html>
