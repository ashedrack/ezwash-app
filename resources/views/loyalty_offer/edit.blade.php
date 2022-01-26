@extends('layouts.app')

@section('title', 'Edit Offer')

@section('page-specific-styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-ui/jquery-ui.min.css') }}">
@endsection

@section('content')
    <section id="basic-form-layouts">
        <div class="row match-height justify-content-md-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" id="basic-layout-form">Edit Offer</h4>
                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body">
                            <form class="form" action="{{route('loyalty_offer.update', ["offer" => $offer->id])}}" method="post" id="editLoyaltyForm">
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
                                        <input type="hidden" value="{{ $offer->id }}" id="offer_id">
                                        @if($authUser->can('list_companies'))
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Company</label>
                                                <input type="text" class="form-control" disabled value="{{ $offer->company->name }}">
                                                <input type="hidden" id="company" value="{{ $offer->company_id }}">
                                            </div>
                                        </div>
                                        @endif
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="offer_name">Offer Name</label>
                                                <input required type="text" name="name" id="offer_name" class="form-control" value="{{ old('name', $offer->display_name) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <label for="spending_requirement">Required Amount</label>
                                            <div class="form-group input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-secondary" id="basic-addon1">₦</span>
                                                </div>
                                                <input type="text" disabled id="spending_requirement" class="form-control" onkeypress="return EzwashHelper.allowOnlyNum(event);" min="1" max="100" value="{{ $offer->spending_requirement }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <label for="discount">Discount to apply</label>
                                            <div class="form-group input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-secondary" id="discount_unit">₦</span>
                                                </div>
                                                <input type="text" disabled id="discount" class="form-control" onkeypress="return EzwashHelper.allowOnlyNum(event);" min="1" max="100" value="{{ $offer->discount_value }}">
                                            </div>
                                        </div>
                                        <br>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="offer_start_date">Offer Start Date</label>
                                                <input type="text" autocomplete="off" readonly disabled id="offer_start_date" class="form-control" value="{{ $offer->start_date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="offer_end_date">Offer End Date</label>
                                                <input type="text" autocomplete="off" readonly disabled id="offer_end_date" class="form-control" value="{{ $offer->end_date }}">
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-sm-12">
                                            <label for="offer_status">Set loyalty offer status</label>
                                            <div class="form-group" id="offer_status">
                                                <label class="radio-inline mr-1">
                                                    <input type="radio" name="status" required @if(old('status', $offerStatus) == 'active') checked @endif id="active_status" value="active">Active
                                                </label>
                                                <label class="radio-inline mr-1">
                                                    <input type="radio" name="status" required @if(old('status', $offerStatus) == 'inactive') checked @endif id="inactive_status" value="inactive">Inactive
                                                </label>
                                            </div>
                                            <input type="checkbox" hidden="hidden" name="force_active" id="force_active">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions text-right">
                                    <button type="submit" class="btn btn-primary col-md-2">
                                        <i class="la la-check-square-o"></i> Update
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
    <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{asset('js/pages/edit-loyalty-offer.js')}}" type="text/javascript"></script>
@endsection
