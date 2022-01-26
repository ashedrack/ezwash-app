@extends('layouts.app')

@section('title', 'General Statistics')

@section('navigation')
    @component('components.navigation.super_admin_navigation');
    @endcomponent
@endsection

@section('content')
    <section id="general-statistics-wrapper">
        <div class="row">
            <div class="col-md-3 col-sm-3">
                <div class="card stats-box-primary">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-center">
                                    <p class="stat-title">Number of Companies</p>
                                    <p class="stat-value" id="number_of_companies">{{ $stats->companies_count }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="card stats-box-primary">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-center">
                                    <p class="stat-title">Number of Locations</p>
                                    <p class="stat-value" id="number_of_locations">{{ $stats->locations_count }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="card stats-box-danger">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-center">
                                    <p class="stat-title">Total Sales</p>
                                    <p class="stat-value naira-prefix" id="total_sales">{{ number_format($stats->all_time_income, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="card stats-box-primary">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-center">
                                    <p class="stat-title">All Customers</p>
                                    <p class="stat-value" id="all_customers">{{ number_format($stats->customers_count, 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12 text-center">
                <form class="card" style="padding: 1em;" id="filter-reports" action="{{ route('statistics.filter') }}">
                    <h2>Filter Reports</h2>
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_company">Company</label>
                                <select name="company" class="form-control" @if($authUser->company_id) readonly @else id="filter_company" @endif>
                                    <option value="">Select Company</option>
                                    @foreach($stats->companies as $company)
                                    <option value="{{ $company->id }}" @if($authUser->company_id == $company->id) selected @endif>{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_location">Location</label>
                                <select name="location" class="form-control" @if($authUser->location_id) readonly @else id="filter_location" @endif>
                                    <option value="">Select Location</option>
                                    @foreach($stats->locations as $location)
                                        <option value="{{ $location->id }}" @if($authUser->location_id == $location->id) selected @endif>{{ $location->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <label>Period Range</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="date" value="{{ request('start_date') }}" name="start_date" id="start_date" class="form-control" placeholder="Start Date">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="date" value="{{ request('end_date') }}" name="end_date" id="end_date" class="form-control" placeholder="End Date">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row d-flex">
                        <div class="col-md-4 m-auto">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Apply Filter</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Filtered Reports-->
        <section id="reports-filter-results">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="card stats-box-primary">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-center">
                                        <p class="stat-title">Sales(<span id="sales_count">{{ $stats->completed_orders }}</span>)</p>
                                        <p class="stat-value naira-prefix" id="sales_by_filter">{{ number_format($stats->total_income, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stats-box-danger">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-center">
                                        <p class="stat-title">Pickup/Delivery Revenue</p>
                                        <p class="stat-value" id="pickup_delivery_income">{{ $stats->pickup_delivery_income }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stats-box-danger">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-center">
                                        <p class="stat-title">Pickup/Delivery Actual Amount</p>
                                        <p class="stat-value" id="actual_pickup_delivery_income">{{ $stats->actual_pickup_delivery_income }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card stats-box-primary">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-center">
                                        <p class="stat-title">Discounts(<span id="discounts_count">{{ $stats->discounts_count }}</span>)</p>
                                        <p class="stat-value naira-prefix" id="discounts_amount">{{ number_format($stats->discounts, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{--<div class="col-md-3 col-sm-6">--}}
                    {{--<div class="card stats-box-danger">--}}
                        {{--<div class="card-content">--}}
                            {{--<div class="card-body">--}}
                                {{--<div class="media d-flex">--}}
                                    {{--<div class="media-body text-center">--}}
                                        {{--<p class="stat-title">Pending Sales(<span id="pending_sales_count">{{ $stats->pending_orders }}</span>)</p>--}}
                                        {{--<p class="stat-value naira-prefix" id="pending_sales">{{ number_format($stats->pending_income, 2) }}</p>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            </div>
            <!--  Payment Methods Reports -->
            <div class="row">
                <div class="col-4">
                    <div class="card stats-box-danger">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-center">
                                        <p class="stat-title">Customers App Transactions (<span id="card_tr_count">{{ $stats->card_income_count }}</span>)</p>
                                        <p class="stat-value naira-prefix" id="card_transactions">{{ number_format($stats->card_income, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card stats-box-primary">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-center">
                                        <p class="stat-title">Cash Transactions(<span id="cash_tr_count">{{ $stats->cash_income_count }}</span>)</p>
                                        <p class="stat-value naira-prefix" id="cash_transactions">{{ number_format($stats->cash_income, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card stats-box-danger">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="media-body text-center">
                                        <p class="stat-title">POS Transactions (<span id="pos_tr_count">{{ $stats->pos_income_count }}</span>)</p>
                                        <p class="stat-value naira-prefix" id="pos_transactions">{{ number_format($stats->pos_income, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
@stop

@section('more-scripts')
    <script src="{{ asset('js/pages/general-statistics.js') }}"></script>
@stop

