<?php
$queryParams = session('orderFilterParams');
?>
<div class="card-body">
    @if(!empty($queryParams) || $orderRequests->total() > 0)
        <div class="card">

            <div class="card-title">
                <div class="width-300 float-right">
                    <a href="{{ $filterUrl }}" class="btn underline">Clear Filter</a>
                    <button class="btn btn-primary float-right" id="toggle_filter_section">Filter Order <span i class="la la-toggle-down"></span></button>
                </div>
            </div>
            <div id="order_filter_section">
                <form id="orders_filter" action="{{ $filterUrl }}" method="get" enctype="multipart/form-data">
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
                            @if(!$authUser->location_id && isset($locations) && !empty($locations))
                                @php
                                    $oldLocation = (array_key_exists('order_location', $queryParams))? old('location',$queryParams['order_location']) : old('location');
                                @endphp
                                <div class="form-group col-md">
                                    <label for="order_location">Location</label>
                                    <select name="order_location" id="order_location" class="form-control">
                                        <option value="">Select Location</option>
                                        @foreach($locations as $l)
                                            <option value="{{ $l->id }}" data-company="{{ $l->id }}" {{ ($oldLocation == $l->id) ? 'selected' : '' }}>{{ $l->company->name . '::' . $l->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="form-group col-md">
                                <?php
                                $oldType = old('order_request_type',$queryParams['order_request_type'] ?? '');
                                ?>
                                <label for="order_request_type">Order Request Type</label>
                                <select name="order_request_type" id="order_request_type" class="form-control text-capitalize">
                                    <option value="">Select Order Request Type</option>
                                    @foreach($orderTypes as $type)
                                        <option value="{{ $type->id }}" {{ ($oldType == $type->id)? 'selected': '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md">
                                <?php
                                $oldStatuses = old('order_request_status', $queryParams['order_request_status'] ?? []);
                                ?>
                                <label for="order_request_status">Order Request Status</label><br>
                                <select name="order_request_status[]" id="order_request_status" multiple class="form-control text-capitalize">
                                    <option value="">Select Order Status</option>
                                    @foreach($orderRequestStatuses as $status)
                                        <option value="{{ $status->id }}" {{ in_array($status->id, $oldStatuses) ? 'selected': '' }}>{{ $status->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md">
                                <label for="kwik_order_id">Kwik Order ID</label>
                                <input class="form-control" name="kwik_order_id" id="kwik_order_id" value="{{ old('kwik_order_id', $queryParams['kwik_order_id'] ?? '') }}" type="text">
                            </div>

                        </div>
                        @if(empty($hideUserFilter))
                            <?php
                            $oldNameOrEmail = (array_key_exists('user_name_or_email', $queryParams))? old('user_name_or_email',$queryParams['user_name_or_email']) : old('user_name_or_email ')
                            ?>
                            <div class="row">
                                <div class="form-group col-md">
                                    <label for="filter_start_date">From Date</label>
                                    <input class="form-control pick-date from-date" readonly value="{{ old('filter_start_date', $queryParams['filter_start_date'] ?? '') }}" type="text" id="filter_start_date" name="filter_start_date">
                                </div>
                                <div class="form-group col-md">
                                    <label for="filter_end_date">To Date</label>
                                    <input class="form-control pick-date to-date" readonly value="{{ old('filter_end_date', $queryParams['filter_end_date'] ?? '') }}" type="text" id="filter_end_date" name="filter_end_date">
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
    {{$orderRequests->appends($queryParams)->links()}}
    <table class="table table-bordered" id="ordersListTable">
        <caption></caption>
        <thead>
        <tr>
            <th scope="col">Customer</th>
            <th scope="col">Email</th>
            <th scope="col">Kwik Order ID</th>
            <th scope="col">Order ID</th>
            <th scope="col">Request Type</th>
            <th scope="col">Amount</th>
            <th scope="col">Status</th>
            <th scope="col">Date</th>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($orderRequests as $key => $order_request)
            <tr>
                <td class="text-capitalize">{{ $order_request->user->name }}</td>
                <td>{{ $order_request->user->email }}</td>
                <td>{{ $order_request->kwik_order_id }}</td>
                <td>{{ $order_request->order_id }}</td>
                <td>{{ ucwords($order_request->order_request_type->name) }}</td>
                <td class="naira-prefix">{{ $order_request->amount }}</td>
                <td class="text-truncate p-1
                    @if(in_array($order_request->order_request_status_id, [\App\Models\OrderRequestStatus::ORDER_DELIVERED, \App\Models\OrderRequestStatus::DROPPED_OFF]))
                        text-success @else text-danger @endif ">
                    {{ ucwords($order_request->order_request_status ? $order_request->order_request_status->display_name : 'Awaiting Payment') }}
                </td>
                <td class="text-truncate p-1">{{ $order_request->created_at }}</td>
                <td class="text-truncate">
                    <a class="btn btn-sm btn-primary round" style="min-width: 100px;" href="{{ route('orderRequest.view', ['order_request' => $order_request->id]) }}">View</a>

                </td>
            </tr>

        @empty
            <tr>
                <td colspan="8" class="text-center">No Order Requests Found</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    {{$orderRequests->appends($queryParams)->links()}}
</div>


