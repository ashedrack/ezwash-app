@extends('layouts.app')

@section('title', 'Location Profile')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header text-center">
                            <h1 id="heading-icon-buttons" class="text-bold-500 pull-left">Location Name: {{$location->name}}</h1>

                            <div class="float-right">
                                <a href="{{ route('location.edit', ['location' => $location->id]) }}" class="btn btn-outline-secondary width-100 mr-1">Edit</a>
                                @if($location->is_active == 0)
                                    <button onclick="EzwashHelper.deactivationWarning('actionForm', '{{ route('location.deactivate', ['location' => $location->id]) }}', 'Current activities by employees will be stopped and login attempts blocked');" class="btn btn-outline-warning float-right width-100">Deactivate</button>
                                @else
                                    <button onclick="EzwashHelper.reactivationWarning('actionForm', '{{ route('location.activate', ['location' => $location->id]) }}', 'Activities on this location will resume')" class="btn btn-outline-warning float-right width-100">Activate</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- eCommerce statistic -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-12">
                    <div class="card pull-up stats-box border-left-5 border-left-primary">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-center">
                                        <h3>{{ $location->users->count() }}</h3>
                                        <h6>Customers</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-12">
                    <div class="card pull-up stats-box border-left-5 border-left-danger">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-center">
                                        <h3 class="naira-prefix">{{ $location->total_sales() }}</h3>
                                        <h6>All-Time Sales</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-12">
                    <div class="card pull-up stats-box border-left-5 border-left-primary">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-center">
                                        <h3 class="naira-prefix">{{ $location->pending_sales() }}</h3>
                                        <h6>Pending Sales</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-12">
                    <div class="card pull-up stats-box border-left-5 border-left-danger">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-center">
                                        <h3 class="naira-prefix">{{ $location->today_sales() }}</h3>
                                        <h6>Sales Today</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                @if ($errors->any())
                    <div class="alert bg-danger">
                        <ul class="display-inline-block">
                            @foreach ($errors->all() as $error)
                                <li class="text-white">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="col-md-12">
                    <div class="card">
                        <ul class="nav nav-tabs nav-linetriangle no-hover-bg">
                            <li class="nav-item">
                                <a class="nav-link active" id="location-orders-link" data-toggle="tab" aria-controls="tab42" href="#location-orders-tab" aria-expanded="false">Orders</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="location-employees-link" data-toggle="tab" aria-controls="tab43" href="#location-employees-tab" aria-expanded="false">Employees</a>
                            </li>
                        </ul>
                    </div>
                    <section class="tab-content">
                        <div id="location-orders-tab" role="tabpanel" class="tab-pane active">
                            <div class="card">
                                @component('components.order.list', [
                                    'allOrders' => $locationOrders,
                                    'orderTypes' => $orderTypes,
                                    'paymentMethods' => $paymentMethods,
                                    'authUser' => $authUser,
                                    'filterUrl' => route('location.view', ['location' => $location->id])
                                ])
                                @endcomponent
                            </div>
                        </div>
                        <div id="location-employees-tab" role="tabpanel" class="tab-pane">
                            <div class="card">
                                @component(
                                    'components.employees_list', array(
                                        'authUser' => $authUser,
                                        'filterUrl' => route('location.view', ['location' => $location->id]),
                                        'allEmployees' => $locationEmployees,
                                        'allRoles' => $roles,
                                    )
                                )
                                @endcomponent
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <!--/ Recent Transactions -->
        </div>
    </div>
    <form method="post" id="actionForm" style="display: none;">
        @csrf
    </form>
@endsection

@section('more-scripts')
    <script src="{{ asset('theme_assets/vendors/js/charts/chartist.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('theme_assets/vendors/js/charts/chartist-plugin-tooltip.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/pages/order-list-component.js') }}"></script>
    <script src="{{asset('js/pages/employee-list-component.js')}}" type="text/javascript"></script>
    <script src="{{ asset('js/pages/view-location.js') }}" type="text/javascript"></script>
@endsection
