@extends('layouts.app')

@section('page-specific-styles')
    <style>
        .swal-title{
            font-size: 20px;
        }
    </style>
@endsection
@section('title', 'All Transactions')

@section('content')
    <!-- Zero configuration table -->
    <section id="configuration">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-center page-title-row">
                        <h1 id="heading-icon-buttons" class="page-heading text-bold-500 pull-left">All Transactions</h1>
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
                        @component('components.transactions', [
                            'authUser' => $authUser,
                            'paymentMethods' => $paymentMethods,
                            'filterUrl' => route('transactions.index'),
                            'transaction_statuses' => $transaction_statuses,
                            'allTransactions' => $allTransactions
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
    <script>
        function confirmStatus(id)
        {
            let transaction_form = $('#confirmTransactionStatus');
            return swal({
                title: "Are you sure you want to confirm this transaction status?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                .then((confirm) => {
                    if (confirm) {
                        $('#transaction_id').val(id);
                        transaction_form.submit();
                    }
                });
        }

        (function ($) {
            @if(session('payment_confirmation_data'))
            const confirmationData = {!! collect(session('payment_confirmation_data')) !!};
            console.log({confirmationData});
            swal({
                title: 'Confirmation Complete',
                icon: confirmationData.status,
                text: 'Result: ' + confirmationData.message
            });

            @endif
        })(jQuery)
    </script>
@endsection

