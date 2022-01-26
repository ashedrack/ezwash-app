<div class="header-navbar navbar-expand-sm navbar navbar-horizontal navbar-fixed navbar-dark navbar-without-dd-arrow navbar-shadow"
     role="navigation" data-menu="menu-wrapper">
    <div class="navbar-container main-menu-content container center-layout" data-menu="menu-container">
        <ul class="nav navbar-nav" id="main-menu-navigation" data-menu="menu-navigation">
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/') }}"><i class="la la-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="dropdown nav-item" data-menu="dropdown">
                <a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="la la-th-list"></i><span>Orders</span></a>
                <ul class="dropdown-menu">
                    <li class="dropdown">
                        <a class="dropdown-item" href="#">All Orders</a>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-item" href="#">Add New</a>
                    </li>
                </ul>
            </li>
            <li class="dropdown nav-item" data-menu="dropdown"><a class="dropdown-toggle nav-link" href="#" data-toggle="dropdown"><i class="la la-users"></i><span>Customers</span></a>
                <ul class="dropdown-menu">
                    <li class="dropdown">
                        <a class="dropdown-item" href="#">All Customers</a>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-item" href="#">Add New</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="la la-tags"></i><span>Loyalty Offers</span></a></li>

            <li class="nav-item">
                <a class="nav-link" href="#"><i class="la la-gears"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>
</div>
