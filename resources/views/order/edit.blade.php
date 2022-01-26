@extends('layouts.app')

@section('title', 'Edit Order')

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
                    <div class="card-header" style="margin: 0 2em;">
                        <h1 class="card-title page-title">Edit Order</h1>
                        @if($order->discount()->exists())
                            <?php
                            $discount = $order->userDiscount;
                            ?>
                            <div class="text-center mt-1" style="background-color: #d6f5ff;">
                                <p class="card-title text-primary">A discount of <span class="naira-prefix">{{ $discount->discount_earned }}</span> will be applied to this order if the <span class="font-weight-bold">card payment method</span> is used</p>
                            </div>
                        @endif
                    </div>
                    <section class="card-content collapse show m-2">

                        <div class="card-body position-relative">
                            @if ($errors->any())
                                <div class="alert bg-danger">
                                    <ul class="display-inline-block">
                                        @foreach ($errors->all() as $error)
                                            <li class="text-white">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
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
                                </table>
                            </section>
                            <section class="col-md-12 services-dropdown-wrapper">
                                <div class="services-dropdown">
                                    <?php
                                    $orderServices = ($order->order_services) ? $order->order_services->toArray() : null;
                                    $oldServices = Arr::pluck($orderServices, 'quantity', 'service_id');
                                    if(old('services')){
                                        $oldServices = Arr::pluck(old('services'), 'quantity', 'id');
                                    }
                                    ?>
                                    @if(!empty($services))
                                        <select class="form-control" multiple id="all-services-options" style="display: none;">
                                            @foreach($services as $service)
                                                @if(!empty($oldServices) && array_key_exists($service->id, $oldServices))
                                                    <option value="{{ $service->id }}" selected data-name="{{ $service->name }}" data-quantity="{{ $oldServices[$service->id] }}" data-price="{{ $service->price }}">{{ $service->name }}</option>
                                                @else
                                                    <option value="{{ $service->id }}" data-name="{{ $service->name }}" data-quantity="1" data-price="{{ $service->price }}">{{ $service->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
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
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                @if($order->discount()->exists() || $order->hasPickupRequest() || $order->hasDeliveryRequest(true))
                                    <tr>
                                        <td colspan="2" class="text-left"></td>
                                        <td class="text-right font-weight-bold">Total</td>
                                        <td class="text-left naira-prefix font-weight-bold" id="total_price">{{ number_format($order->amount_before_discount) }}</td>
                                        <td class="text-left font-weight-bold"></td>
                                    </tr>
                                    @if($order->discount()->exists())
                                        <?php $discount = $order->userDiscount ?>
                                    <tr>
                                        <td colspan="2" class="text-left"></td>
                                        <td class="text-right font-weight-bold">Discount</td>
                                        <td class="text-left text-danger neg-naira-prefix font-weight-bold" id="discount-earned">{{ $discount->discount_earned }}</td>
                                        <td class="text-left font-weight-bold"></td>
                                    </tr>
                                    @endif

                                    @if($order->hasPickupRequest())
                                        <tr>
                                            <td colspan="2" class="text-left"></td>
                                            <td class="text-right font-weight-bold">Pickup Cost</td>
                                            <td class="text-left naira-prefix font-weight-bold" id="pickup-cost">{{ $order->pickup_cost }}</td>
                                            <td class="text-left font-weight-bold"></td>
                                        </tr>
                                    @endif
                                    @if($order->hasDeliveryRequest(true))
                                        <tr>
                                            <td colspan="2" class="text-left"></td>
                                            <td class="text-right font-weight-bold">Delivery Cost</td>
                                            <td class="text-left naira-prefix font-weight-bold" id="delivery-cost">{{ $order->delivery_cost }}</td>
                                            <td class="text-left font-weight-bold"></td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td colspan="2" class="text-left">
                                            <select id="payment_method" name="payment_method" class="form-control payment_methods">
                                                <option value="">Select Payment Method</option>
                                                @foreach($paymentMethods as $method)
                                                    @if($order->payment_method === $method->id)
                                                        <option value="{{ strtolower($method->name)}}" selected>{{ $method->name }}</option>
                                                    @else
                                                        <option value="{{ strtolower($method->name) }}">{{ $method->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-right font-weight-bold">Grand Total</td>
                                        <td class="text-left naira-prefix font-weight-bold" id="grand_total">{{ $order->getAmountToPay() }}</td>
                                        <td class="text-left font-weight-bold"></td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="2" class="text-left">
                                            <select id="payment_method" name="payment_method" class="form-control payment_methods">
                                                <option value="">Select Payment Method</option>
                                                @foreach($paymentMethods as $method)
                                                    @if($order->payment_method === $method->id)
                                                        <option value="{{ strtolower($method->name)}}" selected>{{ $method->name }}</option>
                                                    @else
                                                        <option value="{{ strtolower($method->name) }}">{{ $method->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-right font-weight-bold">Grand Total</td>
                                        <td class="text-left naira-prefix font-weight-bold" id="total_price">{{ $order->amount }}</td>
                                        <td class="text-left font-weight-bold"></td>
                                    </tr>
                                @endif
                                @if((!empty($order->note)))
                                <tr>
                                    <td colspan="5"></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Order Note</td>
                                    <td colspan="4">{{ $order->note }}</td>
                                </tr>
                                @endif
                                </tfoot>
                            </table>
                        </div>

                        @if(!empty($lockers))
                            <?php
                            $orderLockers =($order->lockers) ? $order->lockers->pluck('locker_number')->toArray() : [];
                            $oldLockers = old('lockers', $orderLockers);
//                          ?>
                        <div class="card-body text-center">
                            @foreach($lockers->chunk(10) as $lockersInRow)
                                <div class="row locker-row">
                                    @foreach($lockersInRow as $locker)
                                        @if($locker->occupied === 1 && !in_array($locker->locker_number, $orderLockers))
                                            <div class="col-md locker-box occupied" data-locker-number="{{ $locker->locker_number }}">{{ $locker->locker_number }}</div>
                                        @elseif(in_array($locker->locker_number, $oldLockers))
                                            <div class="col-md locker-box selected" data-locker-number="{{ $locker->locker_number }}">{{ $locker->locker_number }}</div>
                                        @else
                                            <div class="col-md locker-box" data-locker-number="{{ $locker->locker_number }}">{{ $locker->locker_number }}</div>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                            <div class="row locker-row mt-1 height-50">
                                @if(in_array(0, $oldLockers))
                                    <div class="col-md locker-box selected font-weight-bold" data-locker-number="0">Out Of Locker</div>
                                @else
                                    <div class="col-md locker-box font-weight-bold" data-locker-number="0">Out Of Locker</div>
                                @endif
                            </div>
                        </div>
                        @endif
                        <div class="card-body">
                            <form class="form" action="{{ route('order.update',['order' => $order->id ]) }}" method="post" id="editOrderForm">
                                @csrf
                                @if($order->order_type === \App\Classes\Meta::DROP_OFF_ORDER_TYPE)
                                    @foreach($lockers as $locker)
                                        @if(in_array($locker->locker_number, $oldLockers))
                                        <input type="checkbox" checked style="display: none" name="lockers[]" id="locker-input-{{ $locker->locker_number }}" value="{{ $locker->locker_number }}">
                                        @else
                                        <input type="checkbox" style="display: none" name="lockers[]" id="locker-input-{{ $locker->locker_number }}" value="{{ $locker->locker_number }}">
                                        @endif
                                    @endforeach
                                    <input type="checkbox" style="display: none" name="lockers[]" id="locker-input-0" value="0">
                                @endif
                                <button class="btn btn-primary form-control text-white" type="submit">Update Order</button>
                            </form>
                        </div>

                    </section>
                </div>
            </div>
        </div>
    </section>
    <form id="actionForm" method="post" style=" display:none;">
        @csrf
    </form>
@stop

@section('more-scripts')
    <script src="{{ asset('plugins/bootstrap-multiselect/bootstrap-multiselect.js') }}"></script>
    @if($order->order_type === \App\Classes\Meta::SELF_SERVICE_ORDER_TYPE)
        <script src="{{ asset('js/pages/edit-selfservice-order.js') }}"></script>
    @else
        <script src="{{ asset('js/pages/edit-dropoff-order.js') }}"></script>
    @endif
@stop

