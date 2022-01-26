@extends('layouts.app')

@section('title', 'Customer Profile')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">

            <!-- Users Statistics -->
            <div class="row">
                <div class="col-xl-4 col-12" style="padding-bottom: 2em;">
                    <div class="card bg-hexagons d-flex align-items-center" style="height: 100%;">
                        <div class="nav-item dropdown" style="position: absolute;right: 5px;top: 5px;">
                            <a class="nav-link dropdown-toggle btn btn-outline-secondary" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Options</a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('customer.edit', ['customer' => $customer->id]) }}">Edit</a>
                                <div class="dropdown-divider"></div>
                                @if($customer->_isActive())
                                    <a class="dropdown-item" onclick="EzwashHelper.deactivationWarning('actionForm', '{{ route('customer.deactivate', ['customer' => $customer->id]) }}', 'Current activities by this customer will be halted and login attempts blocked');">Deactivate</a>
                                @else
                                    <a class="dropdown-item" onclick="EzwashHelper.reactivationWarning('actionForm', '{{ route('customer.activate', ['customer' => $customer->id]) }}', 'Customer will be able to login and resume operations')">Activate</a>
                                @endif
                            </div>
                        </div>
                        <div class="card-content m-auto">
                            <div class="card-body profile-image-wrapper pt-0">
                                <div class="profile-image-circle" style="background-image: url('{{(!empty($customer->avatar)) ? $customer->avatar : asset('images/default-avatar.jpg') }}');">

                                </div>
                                <h2 class="profile-description">{{ $customer->name }}</h2>
                                <p class="profile-description">{{ $customer->email }}</p>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-12">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card profile-stats-box stats-box border-left-5 border-left-danger">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body text-center">
                                                <p class="stat-value naira-prefix">{{ number_format($transactions[CARD_PAYMENT]->amount, 2) }}</p>
                                                <p class="stat-title">CARD Transactions</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card profile-stats-box stats-box border-left-5 border-left-danger">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body text-center">
                                                <p class="stat-value naira-prefix">{{ number_format($transactions[CASH_PAYMENT]->amount, 2) }}</p>
                                                <p class="stat-title">CASH Transactions</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card profile-stats-box stats-box border-left-5 border-left-danger">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body text-center">
                                                <p class="stat-value naira-prefix">{{ number_format($transactions[POS_PAYMENT]->amount, 2) }}</p>
                                                <p class="stat-title">POS Transactions</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-12">
                            <div class="card profile-stats-box stats-box border-left-5 border-left-danger">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="media d-flex">
                                            <div class="media-body text-center">
                                                <p class="stat-value">{{ $customer->completedOrders()->count() }}</p>
                                                <p class="stat-title">Completed Orders</p>
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
                                                <p class="stat-value naira-prefix">{{ number_format($customer->completedOrders()->sum('amount'), 2) }}</p>
                                                <p class="stat-title">Amount Spent</p>
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
                                                <p class="stat-value">{{ $customer->pendingOrders()->count() }}</p>
                                                <p class="stat-title">Pending Orders</p>
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
                                                <p class="stat-value naira-prefix">{{ $customer->usedDiscounts()->sum('discount_earned') }}</p>
                                                <p class="stat-title">Discount Enjoyed</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                @if($customer->unusedDiscount()->exists())
                    <?php
                    $discount = $customer->unusedDiscount;
                    ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header text-center page-title-row" style="background-color: #d6f5ff;">
                            <p class="card-title text-primary">Customer has earned <span class="naira-prefix">{{ $discount->discount_earned }}</span> off the next wash</p>
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-12">
                    <div class="card box-shadow-0">
                        <div class="card-header">
                            <h4 class="card-title">Recent Orders</h4>
                            <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                        </div>
                        @if ($errors->any())
                            <div class="alert bg-danger">
                                <ul class="display-inline-block">
                                    @foreach ($errors->all() as $error)
                                        <li class="text-white">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @component('components.order.list', [
                            'allOrders' => $customerOrders,
                            'orderTypes' => $orderTypes,
                            'paymentMethods' => $paymentMethods,
                            'hideUserFilter' => true,
                            'authUser' => $authUser,
                            'filterUrl' => route('customer.view', ['customer' => $customer->id])
                        ])
                        @endcomponent
                    </div>
                </div>
            </div>

            <div class="row">
                <div id="recent-activity" class="col-12">
                    @component('components.activity-log', [
                        'allActivities' => $customerActivities,
                        'is_member_activities' => true
                    ])
                    @endcomponent
                </div>
            </div>
        </div>
    </div>
    <form id="actionForm" method="post" style="display:none;">
        @csrf
    </form>
    @component('components.deletion-prompt-template', [
        'permanentDeletionWarning' => 'PLEASE NOTE THAT THIS WILL THE ORDER PERMANENTLY FROM THE DATABASE'
    ])
    @endcomponent
@endsection

@section('more-scripts')
    <script src="{{ asset('js/pages/order-list-component.js') }}"></script>
    <script src="{{ asset('js/deletion_helper.js') }}"></script>
    <script>
        $('#recentActivities').DataTable({
            language : {
                emptyTable: "No Activities Found"
            },
            searching: false,
            paging: false,
            info: false
        });
    </script>
@endsection
