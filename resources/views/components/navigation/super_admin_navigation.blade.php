<div class="header-navbar navbar-expand-sm navbar navbar-horizontal navbar-fixed navbar-dark navbar-without-dd-arrow navbar-shadow"
     role="navigation" data-menu="menu-wrapper">
    <div class="navbar-container main-menu-content container center-layout" data-menu="menu-container">
        <ul class="nav navbar-nav" id="main-menu-navigation" data-menu="menu-navigation">
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/') }}"><i class="la la-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="la la-users"></i><span>Employees</span></a>
                <ul class="dropdown-menu">
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{ route('employee.list') }}">All Employees</a>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{ route('employee.add') }}">Add Employee</a>
                    </li>
                </ul>
            </li>
            <li class="dropdown nav-item" data-menu="dropdown">
                <a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="la la-th-list"></i><span>Orders</span></a>
                <ul class="dropdown-menu">
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{ route('order.list') }}">All Orders</a>
                    </li>
                </ul>
            </li>
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
            <li class="nav-item">
                <a class="nav-link" href="{{ route('service.list') }}"><i class="la la-clipboard"></i><span>Services</span></a>
            </li>
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
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="la la-map-marker"></i><span>Locations</span></a>
                <ul class="dropdown-menu">
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{route('location.list')}}">All Locations</a>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{route('location.add')}}">Add Location</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('statistics.general'

                ) }}"><i class="la la-bar-chart"></i>
                    <span>Statistics</span>
                </a>
            </li>
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="la la-tags"></i><span>Loyalty Offers</span></a>
                <ul class="dropdown-menu">
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{ route('loyalty_offer.list') }}">All Offer</a>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-item" href="{{ route('loyalty_offer.add') }}">Add New</a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('settings.general') }}"><i class="la la-gears"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>
</div>
