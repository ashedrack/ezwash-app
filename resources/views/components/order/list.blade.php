<?php
$queryParams = session('orderFilterParams');
?>
<div class="card-body">
    @if(!empty($queryParams) || $allOrders->total() > 0)
    <div class="card">

        <div class="card-title">
            <div class="width-300 float-right">
                <a href="{{ $filterUrl }}" class="btn underline">Clear Filter</a>
                <button class="btn btn-primary float-right" id="toggle_filter_section">Filter Order <span i class="la la-toggle-down"></span></button>
            </div>
        </div>
        <div id="order_filter_section" class="@if(empty($queryParams)) hide-section @endif">
            <form id="orders_filter" action="{{ $filterUrl }}" method="get">
                @if(isset($hiddenFields) && !empty($hiddenFields))
                    @foreach($hiddenFields as $field)
                    <input type="hidden" name="{{ $field['name'] }}" value="{{ $field['value'] }}">
                    @endforeach
                @endif
                <div class="form-body container">
                    <p>Apply any or all of the filters below to streamline your search</p>
                    <div class="row">
                        @if(!$authUser->company_id && isset($companies) && !empty($companies))
                        <div class="form-group col-md">
                            <label for="order_company">Company</label>
                            <select name="order_company" id="order_company" class="form-control">
                                <option value="">Select Company</option>
                                @foreach($companies as $c)
                                <option value="{{ $c->id }}" {{ (old('order_company', $queryParams['order_company'] ?? '') == $c->id) ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        @if(!$authUser->location_id && isset($allLocations) && !empty($allLocations))
                            @php
                            $oldLocation = (array_key_exists('order_location', $queryParams))? old('location',$queryParams['order_location']) : old('location');
                            @endphp
                        <div class="form-group col-md">
                            <label for="order_location">Location</label>
                            <select name="order_location" id="order_location" class="form-control">
                                <option value="">Select Location</option>
                                @foreach($allLocations as $l)
                                <option value="{{ $l->id }}" data-company="{{ $l->id }}" {{ ($oldLocation == $l->id) ? 'selected' : '' }}>{{ $l->company->name . '::' . $l->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="form-group col-md">
                            <?php
                            $oldType = (array_key_exists('order_type', $queryParams))? old('order_type',$queryParams['order_type']) : old('order_type')
                            ?>
                            <label for="order_type">Order Type</label>
                            <select name="order_type" id="order_type" class="form-control text-capitalize">
                                <option value="">Select Order Type</option>
                                @foreach($orderTypes as $type)
                                <option value="{{ $type->id }}" {{ ($oldType == $type->id)? 'selected': '' }}>{{ $type->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md">
                            <?php
                            $orderStatuses =['pending', 'awaiting_payment', 'completed'];
                            $oldStatus = (array_key_exists('order_status', $queryParams))? old('order_status',$queryParams['order_status']) : old('order_status')
                            ?>
                            <label for="order_status">Order Status</label>
                            <select name="order_status" id="order_status" class="form-control text-capitalize">
                                <option value="">Select Order Status</option>
                                @foreach($orderStatuses as $status)
                                    <option value="{{ $status }}" {{ ($oldStatus == $status) ? 'selected': '' }}>{{ str_replace('_', ' ', $status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md">
                            <?php
                            $oldPaymentMethod = (array_key_exists('payment_method', $queryParams))? old('payment_method',$queryParams['payment_method']) : old('payment_method')
                            ?>
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-control text-capitalize">
                                <option value="">Select Payment Method</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->id }}" {{ ($oldPaymentMethod == $method->id ) ? 'selected': '' }}>{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if(empty($hideUserFilter))
                        <?php
                        $oldNameOrEmail = (array_key_exists('user_name_or_email', $queryParams))? old('user_name_or_email',$queryParams['user_name_or_email']) : old('user_name_or_email ')
                        ?>
                    <div class="row">
                        <div class="form-group col-md">
                            <label for="filter_start_date">From Date</label>
                            <input class="form-control pick-date from-date" readonly value="{{ $queryParams['filter_start_date'] ?? '' }}" type="text" id="filter_start_date" name="filter_start_date">
                        </div>
                        <div class="form-group col-md">
                            <label for="filter_end_date">To Date</label>
                            <input class="form-control pick-date to-date" readonly value="{{ $queryParams['filter_end_date'] ?? '' }}" type="text" id="filter_end_date" name="filter_end_date">
                        </div>
                        <div class="form-group col-md">
                            <label for="order_id">Order ID</label>
                            <input class="form-control" name="order_id" id="order_id" value="{{ old('order_id', $queryParams['order_id'] ?? '') }}" type="text">
                        </div>
                        <div class="form-group col-md">
                            <label for="filter_name_or_email">Enter customer name or email</label>
                            <input class="form-control" value="{{ $oldNameOrEmail }}" type="text" id="filter_name_or_email" name="user_name_or_email" placeholder="Filter by customer name or email">
                        </div>
                    </div>
                    @endif
                    <div class="col-md-6 p-0">
                        <div class="row">
                            <div class="col-md">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="form-group">
                                    <button type="button" class="btn btn-danger w-100" id="hide-order-filter">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
    {{$allOrders->appends($queryParams)->links()}}
    <table class="table table-bordered" id="ordersListTable">
        <thead>
        <tr>
            @if(empty($hideUserFilter))
            <th>Customer/OrderID</th>
            @endif
            <th>Order Type</th>
            <th>Location</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Completed At</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($allOrders as $order)
            @if($order->status === \App\Classes\Meta::ORDER_STATUS_COMPLETED)
                <tr>
                    @if(empty($hideUserFilter))
                    <td class="text-capitalize">{{ $order->user->name }} (#{{ $order->id }})</td>
                    @endif
                    <td>{{ $order->orderType->display_name }}</td>
                    <td>{{ $order->location->name}}</td>
                    <td class="naira-prefix">{{ $order->getAmountToPay() }}</td>
                    <td class="text-success">Completed</td>
                    <td>{{ $order->created_at }}</td>
                    <td>{{ $order->payment_method == \App\Models\PaymentMethod::CARD_PAYMENT ? $order->completed_at : $order->created_at }}</td>
                    <td class="text-truncate">
                        <a class="btn btn-sm btn-primary round" style="min-width: 100px;" href="{{ route('order.view', ['order' => $order->id]) }}">View</a>
                    </td>
                </tr>
            @else
                <tr>
                    @if(empty($hideUserFilter))
                    <td class="text-capitalize">{{ $order->user->name }} (#{{ $order->id }})</td>
                    @endif
                    <td>{{ $order->orderType->display_name }}</td>
                    <td>{{ $order->location->name}}</td>
                    <td class="naira-prefix">{{ $order->getAmountToPay() }}</td>
                    {{--<td class="text-danger">Pending</td>--}}
                    @if($order->amount > 0)
                        <td class="text-warning">Awaiting Payment</td>
                    @else
                        <td class="text-danger">Pending</td>
                    @endif
                    <td>{{ $order->created_at }}</td>
                    <td>{{ $order->payment_method == \App\Models\PaymentMethod::CARD_PAYMENT ? $order->completed_at : $order->created_at }}</td>

                        <td class="text-truncate">
                        <a class="btn btn-sm btn-outline-primary round" href="{{ route('order.view', ['order' => $order->id]) }}">Edit</a>
                        @if($authUser->can('delete_order_permanently'))
                            <button type="button" data-deletion-prompt data-deletion-form="orderDeletionForm" data-deletion-url="{{ route('order.delete',['order' => $order->id]) }}" class="btn btn-sm btn-outline-danger round">Delete</button>
                        @elseif($authUser->can('delete_order'))
                            <button type="button" class="btn btn-sm btn-outline-danger round" onclick="EzwashHelper.deletionWarning('orderDeletionForm', '{{route('order.delete',['order' => $order->id])}}');">Delete</button>
                        @endif
                    </td>
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="6" class="text-center">No Orders Found</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    {{$allOrders->appends($queryParams)->links()}}
</div>
<form id="orderDeletionForm" method="post" style="display:none;">
    @csrf
    <input type="hidden" id="deletion_type" name="deletion_type">
</form>

