@extends('layouts.app')

@section('title', 'New Order')

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
                        <h1 class="card-title page-title">New Order</h1>
                        @if($customer->unusedDiscount()->exists())
                            <?php
                            $discount = $customer->unusedDiscount;
                            ?>
                            <div class="text-center mt-1" style="background-color: #d6f5ff;">
                                <p class="card-title text-primary">A discount of <span class="naira-prefix">{{ $discount->discount_earned }}</span> will be applied to this order if the <span class="font-weight-bold">card payment method</span> is selected</p>
                            </div>
                        @endif
                    </div>
                    <section class="card-content collapse show m-2">
                        @component('components.order_details', [
                                'allServices' => $services,
                                'customer' => $customer,
                                'authUser' => $authUser,
                                'locations' => $locations
                            ])
                            @slot('servicesDropdown')
                                @if(!empty($services))
                                    <?php
                                    $oldServices = old('services', []);
                                    if(!empty($oldServices)){
                                        $oldServices = Arr::pluck($oldServices, 'quantity', 'id');
                                    }
                                    ?>
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
                            @endslot
                            @slot('orderServices')
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
                                    <tr>
                                        <td colspan="2" class="text-left">
                                            <select id="payment_method" name="payment_method" class="form-control payment_methods">
                                                <option value="">Select Payment Method</option>
                                                @foreach($paymentMethods as $method)
                                                    <option value="{{ strtolower($method->name) }}" @if(old('payment_method') == strtolower($method->name)) selected @endif >{{ $method->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-right font-weight-bold">Total Amount</td>
                                        <td class="text-left naira-prefix font-weight-bold" id="total_price">0</td>
                                        <td class="text-left"></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            @endslot

                            @slot('orderForm')
                                <form class="form" action="{{ route('order.save') }}" method="post" id="createOrderForm">
                                    @csrf
                                    <input type="hidden" name="location" id="order_location_input" value="{{ (!$authUser->can('list_locations')) ? old('location',$authUser->location->id) : old('location','') }}">
                                    <input type="hidden" name="user_id" value="{{$customer->id}}">
                                    <button class="btn btn-primary form-control text-white" type="submit">Create Order</button>
                                </form>
                            @endslot
                        @endcomponent
                    </section>
                </div>
            </div>
        </div>
    </section>
@stop

@section('more-scripts')
    <script src="{{ asset('plugins/bootstrap-multiselect/bootstrap-multiselect.js') }}"></script>
    <script src="{{ asset('js/pages/new-order.js') }}"></script>
@stop

