@extends('layouts.app')

@section('title', 'All Order')
@php
    $statisticsResult = session('filteredOrdersAggregate');
@endphp
@section('content')
    <!-- Zero configuration table -->
    <section id="configuration">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-center page-title-row">
                        <h1 id="heading-icon-buttons" class="page-heading text-bold-500 pull-left">All Orders</h1>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-content collapse show">
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
                </div>
                {{-- Aggregates row --}}
                <div class="row">
                    <div class="col-sm-12 col-md-3">
                        <div class="card pull-up stats-box border-left-5 border-left-primary">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-center">
                                            <h3><span class="naira-prefix">{{ number_format($statisticsResult->completed_orders_amount, 2) }}</span> ({{ number_format($statisticsResult->completed_orders_count, 0) }})</h3>
                                            <h6>Orders Completed</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-3">
                        <div class="card pull-up stats-box border-left-5 border-left-danger">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-center">
                                            <h3><span class="naira-prefix">{{ number_format($statisticsResult->pending_orders_amount, 2) }}</span> ({{ number_format($statisticsResult->pending_orders_count, 0) }})</h3>
                                            <h6>Orders Pending</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-content collapse show">
                        @component('components.order.list', [
                            'allOrders' => $orders,
                            'authUser' => $authUser,
                            'allLocations' => $locations,
                            'companies' => $companies,
                            'orderTypes' => $orderTypes,
                            'paymentMethods' => $paymentMethods,
                            'filterUrl' => route('order.list')
                        ])
                        @endcomponent
                    </div>
                </div>
            </div>
        </div>
    </section>
    @component('components.deletion-prompt-template', [
        'permanentDeletionWarning' => 'PLEASE NOTE THAT THIS WILL DELETE THE ORDER IRREVERSIBLY FROM THE DATABASE'
    ])
    @endcomponent
@endsection

@section('more-scripts')
    <script src="{{ asset('js/pages/order-list-component.js') }}"></script>
    <script src="{{ asset('js/deletion_helper.js') }}"></script>
    <script src="{{ asset('js/filter-datepicker-handler.js') }}"></script>
@endsection

