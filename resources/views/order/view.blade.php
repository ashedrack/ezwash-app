@extends('layouts.app')

@section('title', 'View Order')

@section('page-specific-styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/bootstrap-multiselect/bootstrap-multiselect.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/pages/order.css') }}">
    <style>
        .swal-title{
            font-weight: 300;
            font-size: 21px;
        }
    </style>
@endsection

@section('content')
    <section id="configuration">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-8 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title page-title">Order Details</h1>
                    </div>
                    <section class="card-content collapse show m-2">
                        <div class="card-body position-relative">
                            <section class="col-md-12 order-customer">
                                <table class="table table-responsive">
                                    <tr>
                                        <th class="border-right-black">Customer</th>
                                        <td class="text-warning">{{ $order->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="border-right-black">Location</th>
                                        <td class="text-warning">{{ $order->location->name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="border-right-black">Order Type</th>
                                        <td class="text-warning">{{ $order->orderType->display_name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="border-right-black">Order Status</th>
                                        <td class="text-capitalize text-success">{{ $order->order_status->name }}</td>
                                    </tr>
                                </table>
                            </section>
                        </div>
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
                                @if($order->discount()->exists() || $order->hasPickupRequest() || $order->hasDeliveryRequest(true))
                                    <?php $discount = $order->userDiscount ?>
                                    <tr>
                                        <td colspan="2" class="text-left"></td>
                                        <td class="text-right font-weight-bold">Total</td>
                                        <td class="text-left naira-prefix font-weight-bold" id="total_price">{{ $order->amount_before_discount }}</td>
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
                                    @if($order->hasDeliveryRequest(true))
                                        <tr>
                                            <td colspan="2" class="text-left"></td>
                                            <td class="text-right font-weight-bold">Delivery Cost</td>
                                            <td class="text-left naira-prefix font-weight-bold" id="delivery-cost">{{ $order->delivery_cost }}</td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <td colspan="2" class="text-left">
                                            Payment Method: <span class="text-warning text-uppercase">{{ $order->paymentMethod ? $order->paymentMethod->name : '' }}</span>
                                        </td>
                                        <td class="text-right font-weight-bold">Grand Total</td>
                                        <td class="text-left naira-prefix font-weight-bold" id="grand_total">{{ $order->getAmountToPay() }}</td>
                                    </tr>
                                @else
                                    <tr>
                                    <td colspan="2" class="text-left">
                                        Payment Method: <span class="text-warning text-uppercase">{{ $order->paymentMethod ? $order->paymentMethod->name : '' }}</span>
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
                            @if(isset($lockers) && !empty($lockers))
                                <div class="card-body"  style="border: 1px solid #ccc;">
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
                                    <form method="post" id="flagOrderAsCollectedForm" action="{{ route('order.collect', ['order' => $order->id]) }}">
                                        @csrf
                                    </form>
                                    <div class="mt-2">
                                        <a onclick="collectOrder()" href="javascript:void(0);" class="btn btn-primary form-control">Collect Order</a>
                                    </div>
                                @endif
                            @endif
                        </div>
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
    <script>

        function collectOrder() {
            return swal({
                title: "Are you sure this order has been collected?",
                icon: "warning",
                buttons: {
                    cancel : true,
                    confirm: "Confirm",
                },

            })
            .then((confirm) => {
                if (confirm) {
                    $('#flagOrderAsCollectedForm').submit();
                }
            });
        }
    </script>
@stop

