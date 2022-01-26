@extends('layouts.app')

@section('title', $pageTitle)

@section('page-specific-styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-ui/jquery-ui.min.css') }}">
@endsection

@section('content')
    <section id="basic-form-layouts">
        <div class="row match-height justify-content-md-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center page-title-row">
                        <h1 id="heading-icon-buttons" class="page-heading text-bold-500 pull-left">{{ $pageTitle }}</h1>
                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body">
                            <form class="form" action="{{route('special_discount.update', ["offer" => $offer->id])}}" id="specialOfferCreationForm"
                                  method="post">
                                @csrf
                                @if ($errors->any())
                                    <div class="alert bg-danger">
                                        <ul class="display-inline-block">
                                            @foreach ($errors->all() as $error)
                                                <li class="text-white">{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="form-body">
                                    <div class="row">
                                        @if(!$authUser->company_id)
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="company_select">Company</label>
                                                    <input name="company" id="company_select" class="form-control" value="{{ $offer->company->name }}" readonly>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="offer_name">Offer Name</label>
                                                <input type="text" name="name" required id="offer_name"
                                                       value="{{ old('name', $offer->display_name) }}"
                                                       class="form-control"
                                                       placeholder="Easter holiday offer">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="discount">Discount to apply</label>
                                            <div class="form-group input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-secondary"
                                                          id="discount_unit">₦</span>
                                                </div>
                                                <input type="text" name="discount" required id="discount"
                                                       class="form-control" value="{{ old('discount', $offer->discount_value) }}"
                                                       placeholder="100"
                                                       onkeypress="return EzwashHelper.allowOnlyNum(event);"
                                                       aria-describedby="discount_unit" maxlength="10">
                                            </div>
                                        </div>
                                        <br>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="offer_start_date">Offer Start Date</label>
                                                <input type="text" autocomplete="off" required
                                                       value="{{ old('start_date', $offer->start_date) }}" readonly name="start_date"
                                                       id="offer_start_date" class="form-control offer_date_field"
                                                       placeholder="Click to select date">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="offer_end_date">Offer End Date</label>
                                                <input type="text" autocomplete="off" required
                                                       value="{{ old('end_date', $offer->end_date) }}" readonly name="end_date"
                                                       id="offer_end_date" class="form-control offer_date_field"
                                                       placeholder="Click to select end date">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="customersAutocomplete">Search customer by name or email</label>
                                                <input type="text" id="customersAutocomplete" class="form-control"
                                                       autocomplete="off"
                                                       data-source-url="{{ route('customer.autocomplete_search') }}"
                                                       placeholder="Enter customer name or email">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table table-striped table-bordered zero-configuration"
                                               id="offerCustomers">
                                            <caption>The customers selected would be displayed in this table</caption>
                                            <thead>
                                            <tr>
                                                {{--<th scope="col">S/N</th>--}}
                                                <th scope="col">Name</th>
                                                <th scope="col">Email</th>
                                                <th scope="col">Phone</th>
                                                <th scope="col"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if($specialOfferCustomers->isEmpty())
                                                <tr id="emptyCustomer">
                                                    <td colspan="4" class="text-center">No customer selected</td>
                                                </tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="form-actions text-right">
                                    <button type="submit" class="btn btn-primary col-md-2">
                                        <em class="la la-check-square-o"></em> Next
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('more-scripts')
    <script>
        let dateTomorrow = "{{ date('Y-m-d', strtotime('tomorrow')) }}";
        let EXISTING_CUSTOMERS = {!! collect($specialOfferCustomers) !!};
    </script>
    {{--<script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>--}}
    <script src="{{ asset('js/pages/create-special-offer.js') }}" type="text/javascript"></script>
@endsection
