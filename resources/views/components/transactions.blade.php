<?php
$queryParams = session('orderFilterParams');
?>
<div class="card-body">
{{--    @if(!empty($queryParams) || $allOrders->total() > 0)--}}
        <div class="card">

            <div class="card-title">
                <div class="width-300 float-right">
                    <a href="{{ $filterUrl }}" class="btn underline">Clear Filter</a>
                    <button class="btn btn-primary float-right" id="toggle_filter_section">Filter Order <span i class="la la-toggle-down"></span></button>
                </div>
            </div>
            <div id="order_filter_section">
                <form id="orders_filter" action="{{ $filterUrl }}" method="get">
                    <div class="form-body container">
                        <p>Apply any or all of the filters below to streamline your search</p>
                        <div class="row">

                            <div class="form-group col-md">
                                <?php
                                $oldType = (array_key_exists('order_type', $queryParams))? old('order_type',$queryParams['order_type']) : old('order_type')
                                ?>
                                <label for="order_type">Payment Type</label>
                                <select name="payment_method" id="payment_method" class="form-control">
                                    <option value="">Select Payment Type</option>
                                    @foreach($paymentMethods as $type)
                                        <option value="{{ $type->id }}" {{ ($oldType == $type->id)? 'selected': '' }}>{{ ucwords($type->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md">
                                <?php
                                $orderStatuses =['pending', 'awaiting_payment', 'completed'];
                                $oldStatus = (array_key_exists('order_status', $queryParams))? old('order_status',$queryParams['order_status']) : old('order_status')
                                ?>
                                <label for="transaction_status">Transaction Status</label>
                                <select name="transaction_status" id="transaction_status" class="form-control">
                                    <option value="">Select Transaction Status</option>
                                    @foreach($transaction_statuses as $status)
                                        <option value="{{ $status->id }}" {{ ($oldStatus == $status->id) ? 'selected': '' }}>{{ $status->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md">
                                <label for="transactionReference">Transaction Reference</label>
                                <input name="transaction_reference" value="{{ old('transaction_reference') }}" id="transactionReference" class="form-control"/>
                            </div>

                        </div>
                        @if(empty($hideUserFilter))
                            <?php
                            $oldNameOrEmail = (array_key_exists('user_name_or_email', $queryParams))? old('user_name_or_email',$queryParams['user_name_or_email']) : old('user_name_or_email ')
                            ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <input class="form-control" value="{{ $oldNameOrEmail }}" type="text" id="filter_name_or_email" name="user_name_or_email" placeholder="Filter by customer name or email">
                                    </div>
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
{{--    @endif--}}
    {{$allTransactions->appends($queryParams)->links()}}
    <table class="table table-bordered" id="ordersListTable">
        <caption></caption>
        <thead>
        <tr>
            <th scope="col">S/N</th>
            <th scope="col">Customer</th>
            <th scope="col">Amount</th>
            <th scope="col">Transaction Ref</th>
            <th scope="col">Status</th>
            <th scope="col">Payment Type</th>
            <th scope="col">Payment Date</th>
            <th scope="col">Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($allTransactions as $key => $transaction)
                <tr>
                    <td>{{ 'ID'.$transaction->id.' '.($key + 1)  }}</td>
                    <td>{{ $transaction->user->name }}</td>
                    <td class="naira-prefix">{{ $transaction->amount }}</td>
                    <td>@if(!is_null($transaction->reference_code))<code>{{ $transaction->reference_code }}</code> @endif</td>
                    <td class="@if($transaction->transaction_status_id === \App\Models\TransactionStatus::COMPLETED) text-success @else text-info @endif text-capitalize">{{ (!is_null($transaction->transactionStatus)) ? $transaction->transactionStatus->name : '' }}</td>
                    <td class="text-capitalize">{{ $transaction->payment_method->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaction->updated_at)->format('jS \of F Y') }}</td>
                    <td>
                        @if($authUser->can('confirm_transaction') && $transaction->transaction_type_id === \App\Models\TransactionType::ORDER_PAYMENT_ID && $transaction->transaction_status_id !== \App\Models\TransactionStatus::COMPLETED)
                            <a href="javascript:void(0);" onclick="confirmStatus({{ $transaction->id }})" class="btn btn-outline-primary btn-round btn-sm">Verify Transaction</a>
                        @endif
                    </td>
                </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">No Transactions Found</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <form method="post" id="confirmTransactionStatus" action="{{ route('transaction.confirm_status') }}">
        @csrf
        <input name="transaction" hidden id="transaction_id">
    </form>
    {{$allTransactions->appends($queryParams)->links()}}
</div>


