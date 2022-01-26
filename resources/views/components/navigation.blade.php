<div class="header-navbar navbar-expand-sm navbar navbar-horizontal navbar-fixed navbar-dark navbar-without-dd-arrow navbar-shadow"
     role="navigation" data-menu="menu-wrapper">
    <div class="navbar-container main-menu-content container center-layout" data-menu="menu-container">
        <ul class="nav navbar-nav" id="main-menu-navigation" data-menu="menu-navigation">
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/') }}"><i class="la la-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            @if($authUser->can(['create_employee', 'list_employees']))
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="la la-users"></i><span>Employees</span></a>
                <ul class="dropdown-menu">
                    @if($authUser->can(['list_employees']))
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{ route('employee.list') }}">All Employees</a>
                    </li>
                    @endif
                    @if($authUser->can(['create_employee']))
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{ route('employee.add') }}">Add Employee</a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif
            @if($authUser->can('list_orders'))
            <li class="dropdown nav-item" data-menu="dropdown">
                <a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="la la-th-list"></i><span>Orders</span></a>
                <ul class="dropdown-menu">
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{ route('order.list') }}">All Orders</a>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{ route('order_request.list') }}">Pickup and Delivery Requests</a>
                    </li>
                </ul>
            </li>
            @endif
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="la la-users"></i><span>Customers</span></a>
                <ul class="dropdown-menu">
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{ route('customer.list') }}">All Customers</a>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{ route('customer.add') }}">Add New</a>
                    </li>
                </ul>
            </li>
            @if($authUser->can('list_services'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('service.list') }}"><i class="la la-clipboard"></i><span>Services</span></a>
            </li>
            @endif
            @if($authUser->can(['create_company', 'list_companies']))
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="la la-building"></i><span>Companies</span></a>
                <ul class="dropdown-menu">
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{route('company.list')}}">Registered Companies</a>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{route('company.add')}}">Add New</a>
                    </li>
                </ul>
            </li>
            @endif
            @if($authUser->can('create_location') || $authUser->can('list_locations'))
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="la la-map-marker"></i><span>Locations</span></a>
                <ul class="dropdown-menu">
                    @if($authUser->can('list_locations'))
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{route('location.list')}}">All Locations</a>
                    </li>
                    @endif
                    @if($authUser->can('create_location'))
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{route('location.add')}}">Add Location</a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif
            @if($authUser->can('view_statistics'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('statistics.general'

                ) }}"><i class="la la-bar-chart"></i>
                    <span>Statistics</span>
                </a>
            </li>
            @endif
            @if($authUser->can(['create_offer', 'list_offers']))
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="la la-tags"></i><span>Loyalty Offers</span></a>
                <ul class="dropdown-menu">
                    @if($authUser->can('list_offers'))
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{ route('loyalty_offer.list') }}">All Offers</a>
                    </li>
                    @endif
                    @if($authUser->can('create_offer'))
                        <li class="dropdown">
                            <a class="dropdown-item" href="{{ route('loyalty_offer.add') }}">Add New</a>
                        </li>
                    @endif
                    @if($authUser->can('special_discount.list'))
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{ route('special_discount.list') }}">Special Offers</a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif
            @if($authUser->can('list_transactions'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('transactions.index') }}"><em class="la la-money"></em>
                        <span>Transactions</span>
                    </a>
                </li>
            @endif
            @if($authUser->can('view_settings'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('settings.general') }}"><em class="la la-gears"></em>
                    <span>Settings</span>
                </a>
            </li>
            @endif
        </ul>
    </div>
</div>
