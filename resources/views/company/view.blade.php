@extends('layouts.app')

@section('title', 'Company Profile')

@section('page-specific-styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('theme_assets/vendors/charts/chartist.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('theme_assets/vendors/charts/chartist-plugin-tooltip.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/pages/view-company.css') }}">
@endsection


@section('content')
    <section class="content-body">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header text-center">
                        <h2 id="heading-icon-buttons" class="text-bold-500 pull-left">Company Name: {{$company->name}}</h2>

                        <div class="float-right">
                            <a href="{{ route('company.edit', ['company' => $company->id]) }}" class="btn btn-outline-secondary width-100 mr-1">Edit</a>
                            @if($company->_isActive())
                            <button onclick="EzwashHelper.deactivationWarning('actionForm', '{{ route('company.deactivate', ['company' => $company->id]) }}', 'Current activities by employees will be stopped and login attempts blocked');" class="btn btn-outline-warning float-right width-100">Deactivate</button>
                            @else
                                <button onclick="EzwashHelper.reactivationWarning('actionForm', '{{ route('company.activate', ['company' => $company->id]) }}', 'Activities on this company will resume')" class="btn btn-outline-warning float-right width-100">Activate</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Revenue, Hit Rate & Deals -->
        <div class="row">
            <div class="col-xl-6 col-12" id="ecommerceChartView">
                <div class="card card-shadow">
                    <div class="card-header card-header-transparent py-20">
                        <div class="btn-group dropdown">
                            <h3>Statistic</h3>
                        </div>
                        <ul class="nav nav-pills nav-pills-rounded chart-action float-right btn-group" role="group">
                            <li class="nav-item"><a class="active nav-link" data-toggle="tab" href="#scoreLineToDay">Daily</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#scoreLineToWeek">Weekly</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#scoreLineToMonth">Monthly</a></li>
                        </ul>
                    </div>
                    <div class="widget-content tab-content bg-white p-20">
                        <div class="ct-chart tab-pane active scoreLineShadow" id="scoreLineToDay"></div>
                        <div class="ct-chart tab-pane scoreLineShadow" id="scoreLineToWeek"></div>
                        <div class="ct-chart tab-pane scoreLineShadow" id="scoreLineToMonth"></div>
                    </div>
                </div>
                <!--/ Products sell and New Orders -->
            </div>
            <div class="col-xl-6 col-12 d-flex align-items-center">
                <div class="row">
                    <div class="col-lg-4 col-12">
                        <div class="card profile-stats-box stats-box border-left-5 border-left-danger">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-center">
                                            <h3 class="naira-prefix">{{ $statistics->cash_income }}</h3>
                                            <h6>Cash Transactions</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="card profile-stats-box stats-box border-left-5 border-left-primary">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-center">
                                            <h3 class="naira-prefix">{{ $statistics->pos_income }}</h3>
                                            <h6>POS Transactions</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="card profile-stats-box stats-box border-left-5 border-left-danger">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-center">
                                            <h3 class="naira-prefix">{{ $statistics->card_income }}</h3>
                                            <h6>Card Transactions</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12">
                        <div class="card profile-stats-box stats-box border-left-5 border-left-danger">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-center">
                                            <h3>{{ $statistics->completed_orders }}</h3>
                                            <h6>Completed Orders</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12">
                        <div class="card profile-stats-box stats-box border-left-5 border-left-primary">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-center">
                                            <h3 class="naira-prefix">{{ $statistics->total_income }}</h3>
                                            <h6>Total Income</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-12">
                        <div class="card profile-stats-box stats-box border-left-5 border-left-primary">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-center">
                                            <h3>{{ $statistics->pending_orders }}</h3>
                                            <h6>Pending Orders</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12">
                        <div class="card profile-stats-box stats-box border-left-5 border-left-danger">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-center">
                                            <h3 class="naira-prefix">{{ $statistics->pending_income }}</h3>
                                            <h6>Pending Income</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Revenue, Hit Rate & Deals -->

        @php
            $activeTab = request('active_tab') ?? 'company-locations-tab';
        @endphp
        <section class="row">
            <section class="col-lg-12">
                <div class="card">
                    <ul class="nav nav-tabs nav-linetriangle no-hover-bg">
                    {{--<ul class="nav nav-tabs nav-justified nav-top-border no-hover-bg">--}}
                        <li class="nav-item">
                            <a class="nav-link @if($activeTab == 'company-locations-tab') active @endif" id="company-locations-link" data-toggle="tab" aria-controls="tab41" href="#company-locations-tab" aria-expanded="@if($activeTab == 'company-locations-tab') true @else false @endif">Locations</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if($activeTab == 'company-orders-tab') active @endif" id="company-orders-link" data-toggle="tab" aria-controls="tab42" href="#company-orders-tab" aria-expanded="@if($activeTab == 'company-orders-tab') true @else false @endif">Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if($activeTab == 'company-employees-tab') active @endif" id="company-employees-link" data-toggle="tab" aria-controls="tab43" href="#company-employees-tab" aria-expanded="@if($activeTab == 'company-employees-tab') true @else false @endif">Employees</a>
                        </li>
                    </ul>
                </div>
                <section class="tab-content">
                    <div id="company-locations-tab" role="tabpanel" class="tab-pane @if($activeTab == 'company-locations-tab') active @endif">
                        <div class="row w-100">
                        @forelse($company->locations as $location)
                            <div class="col-xl-3 col-md-4 col-12">
                                <div class="card profile-card-with-cover">
                                    <div class="card-img-top img-fluid bg-cover height-200" style="background: url('{{ $location->store_image }}') no-repeat center/cover !important;">
                                        <div class="blur-bg"></div>
                                    </div>
                                    <div class="profile-card-with-cover-content text-center">
                                        <div class="card-body">
                                            <h2 class="card-title" aria-label="Location Name">{{ $location->name }}</h2>
                                            <p class="card-subtitle text-muted" aria-label="Phone number for this location">{{ $location->phone }}</p>
                                        </div>
                                        <div class="container mb-1">
                                            <div class="col-12 mb-1">
                                                <a href="{{route('location.view', ['location' => $location->id])}}" class="btn btn-primary btn-md col-12"> View </a>
                                            </div>
                                            <div class="container d-flex align-items-center">
                                                <div class="location-mod-btn col-md-6 pad-right-5px">
                                                    <a href="{{ route('location.edit', ['location' => $location->id]) }}" class="btn btn-outline-secondary btn-md">Edit</a>
                                                </div>
                                                <div class="location-mod-btn col-md-6 pad-left-5px">
                                                    <button type="button" data-deletion-prompt data-deletion-form="actionForm" data-deletion-url="{{ route('location.delete',['location' => $location->id]) }}" class="btn btn-outline-danger btn-md"> Delete</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @component('components.deletion-prompt-template', [
                                'permanentDeletionWarning' => 'PLEASE NOTE THAT THIS WILL DELETE THE LOCATION, ALL EMPLOYEES, AND ORDERS ASSOCIATED WITH IT FROM THE DATABASE'
                            ])
                            @endcomponent
                        @empty
                            <div class="col-12 b">
                                <div class="card" style="background-color: #ccc;">
                                    <div class="card-body text-center">
                                        <p class="text-bold-400 text-black"> No Locations Found</p>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                        </div>
                        <form method="post" id="actionForm" style="display: none;">
                            @csrf
                        </form>
                    </div>
                    <div id="company-orders-tab" role="tabpanel" class="tab-pane @if($activeTab == 'company-orders-tab') active @endif">
                        <div class="card card-dashboard">
                            @component('components.order.list', [
                                    'allOrders' => $companyOrders,
                                    'orderTypes' => $orderTypes,
                                    'paymentMethods' => $paymentMethods,
                                    'authUser' => $authUser,
                                    'allLocations' => $company->locations,
                                    'filterUrl' => route('company.view', ['company' => $company->id]),
                                    'hiddenFields' => [['name' => 'active_tab', 'value' => 'company-orders-tab']]
                                ])
                            @endcomponent
                        </div>
                    </div>
                    <div id="company-employees-tab" role="tabpanel" class="tab-pane @if($activeTab == 'company-employees-tab') active @endif">
                        <div class="card">
                            @component(
                                'components.employees_list', array(
                                    'authUser' => $authUser,
                                    'filterUrl' => route('company.view', ['company' => $company->id]),
                                    'allEmployees' => $companyEmployees,
                                    'allLocations' => $company->locations,
                                    'allRoles' => $roles,
                                    'hiddenFields' => [['name' => 'active_tab', 'value' => 'company-employees-tab']]
                                )
                            )
                            @endcomponent
                        </div>
                    </div>
                </section>
            </section>
        </section>
    </section>
    <form method="post" id="actionForm" style="display: none;">
        @csrf
    </form>
    @component('components.deletion-prompt-template', [
        'permanentDeletionWarning' => 'PLEASE NOTE THAT THIS WILL DELETE THE LOCATION, ALL EMPLOYEES, AND ORDERS ASSOCIATED WITH IT FROM THE DATABASE'
    ])
    @endcomponent
@endsection

@section('more-scripts')
    <script src="{{ asset('theme_assets/vendors/js/charts/chartist.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('theme_assets/vendors/js/charts/chartist-plugin-tooltip.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/deletion_helper.js') }}"></script>
    <script src="{{ asset('js/pages/order-list-component.js') }}"></script>
    <script src="{{asset('js/pages/employee-list-component.js')}}" type="text/javascript"></script>
    <script src="{{ asset('js/pages/view-company.js') }}" type="text/javascript"></script>
@endsection
