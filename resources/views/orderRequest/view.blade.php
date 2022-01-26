@extends('layouts.app')

@section('title', 'View Order')

@section('page-specific-styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/bootstrap-multiselect/bootstrap-multiselect.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/pages/order.css') }}">
@endsection

@section('content')
    <!-- Zero configuration table -->
    <section id="configuration">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title page-title">Order Request Details</h1>
                    </div>
                    <section class="card-content collapse show m-2">
                        <div class="card-body position-relative">
                            <section class="col-md-12 order-customer">
                                <table class="table table-responsive">
                                    <tr>
                                        <th class="border-right-black">Customer</th>
                                        <td class="text-secondary">{{ $order_request->user->name }}</td>
                                    </tr>
                                    @if($order_request->order_request_type_id === \App\Models\OrderRequestType::PICKUP)
                                    <tr>
                                        <th class="border-right-black">Pickup Address</th>
                                        <td class="text-secondary">{{ $order_request->address->address }}</td>
                                    </tr>
                                    <tr>
                                        <th class="border-right-black">DropOff Location</th>
                                        <td class="text-secondary">{{ $order_request->location->name }}</td>
                                    </tr>
                                    @else
                                        <tr>
                                            <th class="border-right-black">Order Location</th>
                                            <td class="text-secondary">{{ $order_request->location->name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="border-right-black">Delivery Address</th>
                                            <td class="text-secondary">{{ $order_request->address->address }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th class="border-right-black">Request Type</th>
                                        <td class="text-capitalize text-warning">{{ $order_request->order_request_type->name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="border-right-black">Request Status</th>
                                        <td class="text-capitalize text-info">{{ $order_request->order_request_status ? $order_request->order_request_status->display_name : 'Awaiting Payment' }}</td>
                                    </tr>
                                    @if($order_request->order_id)
                                    <tr>
                                        <th class="border-right-black">Order Type</th>
                                        <td class="text-warning">{{ $order->orderType->display_name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="border-right-black">Order Status</th>
                                        <td class="text-capitalize text-info">{{ $order->order_status->name }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th class="border-right-black">Note</th>
                                        <td class="text-secondary">{{ $order_request->note }}</td>
                                    </tr>
                                    <tr>
                                        <th class="border-right-black">Date</th>
                                        <td class="text-secondary">{{ $order_request->created_at }}</td>
                                    </tr>
                                </table>
                            </section>
                        </div>
                        @if($order_request->order_id)
                        <div class="card-body">
                            <table class="table table-bordered zero-configuration Datatable" id="order_services">
                                <thead>
                                <tr>
                                    <th>Services</th>
                                    <th>Unit Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($order->order_services as $service)
                                    <tr>
                                        <td>{{ $service->service->name }}</td>
                                        <td>{{ $service->price }}</td>
                                        <td>{{ $service->quantity }}</td>
                                        <td>{{ $service->price * $service->quantity }}</td>
                                    </tr>
                                @empty
                                @endforelse
                                </tbody>
                                <tfoot>
                                @if($order->discount()->exists() || $order->hasPickupRequest() || $order->hasDeliveryRequest())
                                    <?php $discount = $order->userDiscount ?>
                                    <tr>
                                        <td colspan="2" class="text-left"></td>
                                        <td class="text-right font-weight-bold">Total</td>
                                        <td class="text-left naira-prefix font-weight-bold" id="total_price">{{ !is_null($order->amount_before_discount) ? $order->amount_before_discount : 0 }}</td>
                                    </tr>
                                    @if($order->discount()->exists())
                                        <?php $discount = $order->userDiscount ?>
                                        <tr>
                                            <td colspan="2" class="text-left"></td>
                                            <td class="text-right font-weight-bold">Discount</td>
                                            <td class="text-left text-danger neg-naira-prefix font-weight-bold" id="discount-earned">{{ $discount->discount_earned }}</td>
                                        </tr>
                                    @endif

                                    @if($order->hasPickupRequest())
                                        <tr>
                                            <td colspan="2" class="text-left"></td>
                                            <td class="text-right font-weight-bold">Pickup Cost</td>
                                            <td class="text-left naira-prefix font-weight-bold" id="pickup-cost">{{ $order->pickup_cost }}</td>
                                        </tr>
                                    @endif
                                    @if($order->hasDeliveryRequest())
                                        <tr>
                                            <td colspan="2" class="text-left"></td>
                                            <td class="text-right font-weight-bold">Delivery Cost</td>
                                            <td class="text-left naira-prefix font-weight-bold" id="delivery-cost">{{ $order->delivery_cost }}</td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <td colspan="2" class="text-left">
                                            Payment Method: <span class="text-warning text-uppercase">{{ $order->paymentMethod ? $order->paymentMethod->name: '' }}</span>
                                        </td>
                                        <td class="text-right font-weight-bold">Grand Total</td>
                                        <td class="text-left naira-prefix font-weight-bold" id="grand_total">{{ $order->getAmountToPay() }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="2" class="text-left">
                                            Payment Method: <span class="text-warning text-uppercase">{{ $order->paymentMethod ? $order->paymentMethod->name: '' }}</span>
                                        </td>
                                        <td class="text-right font-weight-bold">Grand Total</td>
                                        <td class="text-left naira-prefix font-weight-bold" id="total_price">{{ $order->amount }}</td>
                                    </tr>
                                @endif
                                @if((!empty($order->note)))
                                    <tr>
                                        <td colspan="4"></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Order Note</td>
                                        <td colspan="3">{{ $order->note }}</td>
                                    </tr>
                                @endif
                                </tfoot>
                            </table>
                        </div>

                        <div class="card" style="margin: 0 2.5em">
                            @if($order->lockers()->count() > 0)
                                <div class="card-body"  style="border: 1px solid #ccc;">
                                    <?php
                                    $lockers = $order->lockers->pluck('locker_number')->toArray();
                                    ?>
                                    <p>Lockers</p>
                                    <div class="row locker-row d-flex justify-content-start text-center">
                                        @foreach($lockers as $locker)
                                            @if($locker !== 0)
                                                <div class="col-md-1 col-sm-2 locker-box selected">{{ $locker }}</div>
                                            @endif
                                        @endforeach
                                        @if(in_array(0, $lockers))
                                            <div class="col-md-2 locker-box selected">Out of locker</div>
                                        @endif
                                    </div>
                                </div>
                                @if($order->order_type === \App\Classes\Meta::DROP_OFF_ORDER_TYPE && !$order->collected)
                                    <div class="mt-2">
                                        <a href="javascript:void(0);" class="btn btn-primary form-control">Collect Order</a>
                                    </div>
                                @endif
                            @endif
                        </div>
                        @endif
                    </section>
                </div>
            </div>

        </div>
    </section>
    <form id="actionForm" method="post" style="display:none;">
        @csrf
    </form>
@stop

@section('more-scripts')
    <script src="{{ asset('plugins/bootstrap-multiselect/bootstrap-multiselect.js') }}"></script>
    <script src="{{ asset('js/pages/view-order.js') }}"></script>
@stop

