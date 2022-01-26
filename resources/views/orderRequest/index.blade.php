@extends('layouts.app')

@section('title', 'All Order')

@section('page-specific-styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/bootstrap-multiselect/bootstrap-multiselect.css') }}">
    <style>
        .multiselect.dropdown-toggle.btn{
            border: 1px solid #cacfe7;
            background-color: #fff;
            color: #3b4781;
            width: 100%;
        }
        .multiselect-native-select .btn-group {
            width: 100%;
        }
        .multiselect-container>li>a>label {
            padding: 3px 10px 3px 10px;
        }
    </style>
    {{--<link rel="stylesheet" type="text/css" href="{{ asset('css/pages/order.css') }}">--}}
@endsection

@section('content')
    <!-- Zero configuration table -->
    <section id="configuration">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-center page-title-row">
                        <h1 id="heading-icon-buttons" class="page-heading text-bold-500 pull-left">All Order Requests</h1>
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
                        @component('components.order.order_requests', [
                            'orderRequests' => $orderRequests,
                            'authUser' => $authUser,
                            'orderTypes' => $orderTypes,
                            'filterUrl' => route('order_request.list'),
                            'orderRequestStatuses' => $orderRequestStatuses,
                            'locations' => $locations,
                            'companies' => $companies,
                        ])
                        @endcomponent
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('more-scripts')
    <script src="{{ asset('plugins/bootstrap-multiselect/bootstrap-multiselect.js') }}"></script>
    <script src="{{ asset('js/pages/order-list-component.js') }}"></script>
    <script>
        function cancelRequest() {
            return swal({
                title: "Are you sure?",
                text: "You're about to cancel a request, please confirm this.",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                .then((continueCancel) => {
                    if (continueCancel) {
                        let this_form = $('#cancelRequestForm');
                        this_form.submit();
                    }
                });
        }
    </script>
    <script src="{{ asset('js/filter-datepicker-handler.js') }}"></script>
@endsection

