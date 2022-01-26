
@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-specific-styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('theme_assets/vendors/charts/chartist.css') }}">
    <link rel="stylesheet" type="text/css"
          href="{{ asset('theme_assets/vendors/charts/chartist-plugin-tooltip.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/pages/dashboard.css') }}">
    <style>
        .avatar_shaped {
            border-radius: 100% !important; width: 50px !important;height: 50px !important;
        }
        a.stats-box  {
            color: #6B6F82 !important;
        }
        .stat-title {
            font-size: 18px !important;
            margin-bottom: 1em;
        }
        .stats-box .progress {
            height:2px;
            border-radius:50px;
        }
    </style>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">
        <!-- eCommerce statistic -->
            <div class="row">
                <!-- tab 1 -->
                <div class="col-md">
                    <a href="{{ route('order.list', ['filter_start_date' => $statisticsResult->firstOrderDate, 'filter_end_date' => $statisticsResult->latestOrderDate, 'order_status' => 'completed']) }}" class="card stats-box border-top-5 border-top-primary">
                        <div class="card-body pb-0">
                            <div class="text-left">
                                <h3 class="stat-title">Total Orders Breakdown</h3>
                            </div>
                        </div>
                        <hr class="m-0">
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="m-0 p-0 sm-text">Completed  <span class="naira-prefix">{{ $statisticsResult->totalOrdersBreakdown->received_income }}</span></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0;border-radius:50px; background-color: #8E0000 !important;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="">
                                <p class="m-0 p-0 sm-text">Pending  <span class="naira-prefix">{{ $statisticsResult->totalOrdersBreakdown->pending_income }}</span></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0;border-radius:50px; background-color: #8E0000;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <!--end card-body-->
                    </a>
                    <!--end card-->
                </div>
                <!--end col-->

                <!-- tab 2 -->
                <div class="col-md">
                    <a href="{{ route('order.list', ['filter_start_date' => $statisticsResult->startOfMonth, 'filter_end_date' => $statisticsResult->endOfMonth, 'order_status' => 'completed']) }}" class="card stats-box border-top-5 border-top-danger">
                        <div class="card-body pb-0">
                            <div class="text-left">
                                <h3 class="stat-title"> Orders Breakdown: {{ $statisticsResult->current_month }}</h3>
                            </div>
                        </div>
                        <hr class="m-0">
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="m-0 p-0 sm-text">Completed  <span class="naira-prefix">{{ $statisticsResult->ordersBreakdownThisMonth->received_income }}</span></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0;border-radius:50px; background-color: #8E0000 !important;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="">
                                <p class="m-0 p-0 sm-text">Pending  <span class="naira-prefix">{{ $statisticsResult->ordersBreakdownThisMonth->pending_income }}</span></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0;border-radius:50px; background-color: #8E0000;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <!--end card-body-->
                    </a>
                    <!--end card-->
                </div>
                <!--end col-->

                <!-- tab 3 -->
                <div class="col-md">
                    <a href="{{ route('order.list', ['filter_start_date' => now()->startOfDay()->toDateString(), 'filter_end_date' => now()->endOfDay()->toDateString(), 'order_status' => 'completed']) }}" class="card stats-box border-top-5 border-top-primary">
                        <div class="card-body pb-0">
                            <div class="text-left">
                                <h3 class="stat-title"> Orders Breakdown: Today</h3>
                            </div>
                        </div>
                        <hr class="m-0">
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="m-0 p-0 sm-text">Completed <span class="naira-prefix">{{ $statisticsResult->ordersBreakdownToday->received_income }}</span></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0;border-radius:50px; background-color: #8E0000 !important;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="">
                                <p class="m-0 p-0 sm-text">Pending <span class="naira-prefix">{{ $statisticsResult->ordersBreakdownToday->pending_income }}</span></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0;border-radius:50px; background-color: #8E0000;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <!--end card-body-->
                    </a>
                    <!--end card-->
                </div>
                <!--end col-->


                <!-- tab 4 -->
                <div class="col-md">
                    <a href="{{ route('order_request.list', ['filter_start_date' => $statisticsResult->firstOrderDate, 'filter_end_date' => $statisticsResult->latestOrderDate, 'order_request_status' => [\App\Models\OrderRequestStatus::DROPPED_OFF, \App\Models\OrderRequestStatus::ORDER_DELIVERED]]) }}" class="card stats-box border-top-5 border-top-danger">
                        <div class="card-body pb-0">
                            <div class="text-left">
                                <h3 class="stat-title"> Total Pickups & Deliveries</h3>
                            </div>
                        </div>
                        <hr class="m-0">
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="m-0 p-0 sm-text">Paid <span class="naira-prefix">{{ $statisticsResult->pickupDeliveryStats->total->amountReceived }}</span></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0;border-radius:50px; background-color: #8E0000 !important;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="">
                                <p class="m-0 p-0 sm-text">Unpaid <span class="naira-prefix">{{ $statisticsResult->pickupDeliveryStats->total->amountPending }}</span></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0;border-radius:50px; background-color: #8E0000;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <!--end card-body-->
                    </a>
                    <!--end card-->
                </div>
                <!--end col-->
                <div class="col-md">
                    <a href="{{ route('order_request.list', ['filter_start_date' => $statisticsResult->startOfMonth, 'filter_end_date' => $statisticsResult->endOfMonth, 'order_request_status' => [\App\Models\OrderRequestStatus::DROPPED_OFF, \App\Models\OrderRequestStatus::ORDER_DELIVERED]]) }}" class="card stats-box border-top-5 border-top-primary">
                        <div class="card-body pb-0">
                            <div class="text-left">
                                <h3 class="stat-title"> Pickups & Deliveries: {{ $statisticsResult->current_month }}</h3>
                            </div>
                        </div>
                        <hr class="m-0">
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="m-0 p-0 sm-text">Paid <span class="naira-prefix">{{ $statisticsResult->pickupDeliveryStats->this_month->amountReceived }}</span></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0;border-radius:50px; background-color: #8E0000 !important;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="">
                                <p class="m-0 p-0 sm-text">Unpaid <span class="naira-prefix">{{ $statisticsResult->pickupDeliveryStats->this_month->amountPending }}</span></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0;border-radius:50px; background-color: #8E0000;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <!--end card-body-->
                    </a>
                    <!--end card-->
                </div>

            </div>

            <!-- Products sell and New Orders -->
            <div class="row match-height">
                <div class="col-xl-8 col-12" id="ecommerceChartView">
                    <div class="card card-shadow">
                        <div class="card-header card-header-transparent py-20">
                            <div class="btn-group">
                                <a href="#" class="text-body blue-grey-700" >REVENUE</a>

                            </div>

                        </div>
                        <div class="widget-content tab-content bg-white p-20">
                            <canvas id="area-chart" height="500"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Top Customers</h4>
                            <a class="heading-elements-toggle"><em class="la la-ellipsis-v font-medium-3"></em></a>
                        </div>
                        <div class="card-content">
                            <div id="new-orders" class="media-list position-relative">
                                <div class="table-responsive">
                                    <table id="new-orders-table" class="table table-hover table-xl mb-0">
                                        <caption></caption>
                                        <thead>
                                        <tr>
                                            <th scope="col" class="border-top-0">Avatar</th>
                                            <th scope="col" class="border-top-0">Customer</th>
                                            <th scope="col" class="border-top-0 text-right">Amount Spent</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($topCustomers as $customer)
                                            <tr>
                                                <td>
                                                    <span style=""   class="avatar avatar_shaped avatar-online"><img
                                                            class="avatar_shaped" src="{{ $customer->avatar ?? asset('/images/default_employee_avatar.png') }}" alt="customer avatar"></span>
                                                </td>
                                                <td class="text-truncate pl-1 pr-1"><a href="{{ route('customer.view', ['customer' => $customer->id]) }}">{{ $customer->name }}</a></td>
                                                <td class="text-truncate naira-prefix">{{ number_format($customer->total, 2) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ Products sell and New Orders -->

        </div>
    </div>
@endsection

@section('more-scripts')
{{--    <script src="{{ asset('theme_assets/vendors/js/charts/chartist.min.js')}}" type="text/javascript"></script>--}}
{{--    <script src="{{ asset('theme_assets/vendors/js/charts/chartist-plugin-tooltip.min.js') }}"--}}
{{--            type="text/javascript"></script>--}}
    <script src="{{ asset('js/pages/dashboard.js') }}" type="text/javascript"></script>
<script src="{{asset('js/chart.min.js')}}" type="text/javascript"></script>

<script type="text/javascript">//Chart Section
    /*=========================================================================================
      File Name: home.blade.php
      Description: Chartjs line area chart
      ----------------------------------------------------------------------------------------
    ==========================================================================================*/

    // Line area chart
    // ------------------------------
    $(window).on("load", function(){

        //Get the context of the Chart canvas element we want to select
        var ctx = $("#area-chart");
        const monthlyRevenueLabels = {!! $statisticsResult->monthly_income_report->pluck('label') !!};
        const monthlyRevenueData = {!! $statisticsResult->monthly_income_report->pluck('income') !!};
        const pickupDeliveryRevenueDate = {!! $statisticsResult->monthly_income_report->pluck('pickup_delivery_income') !!};

        console.log({monthlyRevenueLabels, monthlyRevenueData});
        // Chart Options
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom',
            },
            hover: {
                mode: 'label'
            },
            scales: {
                xAxes: [{
                    display: true,
                    gridLines: {
                        color: "rgba(0,0,255,0.1)",
                        drawTicks: true,
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Month'
                    }
                }],
                yAxes: [{
                    display: true,
                    gridLines: {
                        color: "#f3f3f3",
                        drawTicks: false,
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Revenue(₦)'
                    }
                }]
            },
            title: {
                display: true,
                text: 'Revenue for the Year {{ $statisticsResult->current_year }} - (₦)'
            }
        };

        // Chart Data
        const chartData = {
            labels: monthlyRevenueLabels,
            datasets: [
                {
                    label: "Revenue (₦) ",
                    data: monthlyRevenueData,
                    backgroundColor: "rgba(0,0,255,0.3)",
                    borderColor: "transparent",
                    pointBorderColor: "rgba(255,255,255,0.2)",
                    pointBackgroundColor: "rgba(0,0,255,0.3)",
                    pointBorderWidth: 5,
                    pointHoverBorderWidth: 2,
                    pointRadius: 4,
                },

                {
                    label: "Pickup & Delivery (₦) ",
                    data: pickupDeliveryRevenueDate,
                    backgroundColor: "rgba(255,0,236,0.3)",
                    borderColor: "transparent",
                    pointBorderColor: "rgba(255,255,255,0.2)",
                    pointBackgroundColor: "rgba(255,0,236,0.3)",
                    pointBorderWidth: 5,
                    pointHoverBorderWidth: 2,
                    pointRadius: 4,
                }
            ]
        };

        const config = {
            type: 'line',

            // Chart Options
            options : chartOptions,

            // Chart Data
            data : chartData
        };

        // Create the chart
        var areaChart = new Chart(ctx, config);

    });
</script>
@endsection
