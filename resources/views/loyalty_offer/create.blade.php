@extends('layouts.app')

@section('title', 'Add Loyalty Offer')

@section('page-specific-styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-ui/jquery-ui.min.css') }}">
@endsection

@section('content')
    <section id="basic-form-layouts">
        <div class="row match-height justify-content-md-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center page-title-row">
                        <h1 id="heading-icon-buttons" class="page-heading text-bold-500 pull-left">Add Loyalty Offer</h1>
                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body">
                            <form class="form" action="{{route('loyalty_offer.save')}}" id="addLoyaltyForm" method="post">
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
                                        @if($authUser->can('list_companies'))
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="company_select">Company</label>
                                                    <select name="company" id="company_select" class="form-control" required>
                                                        <option value="">Select a company</option>
                                                        @foreach($companies as $company)
                                                            <?php
                                                                $minDate = $company->loyaltyOffers()->max('end_date');
                                                            ?>
                                                            @if(!is_null(old('company')) &&  old('company') == $company->id)
                                                                <option value="{{ $company->id }}" data-minstartdate="{{ $minDate }}" selected>{{ $company->name }}</option>
                                                            @else
                                                                <option value="{{ $company->id }}" data-minstartdate="{{ $minDate }}">{{ $company->name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="offer_name">Offer Name</label>
                                                <input type="text" name="name" required id="offer_name" value="{{ old('name') }}" class="form-control" placeholder="Easter holiday offer">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <label for="spending_requirement">Required Amount</label>
                                            <div class="form-group input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-secondary" id="basic-addon1">₦</span>
                                                </div>
                                                <input type="text" name="spending_requirement" required id="spending_requirement" value="{{ old('spending_requirement') }}" class="form-control" placeholder="3000" onkeypress="return EzwashHelper.allowOnlyNum(event);" maxlength="10">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-12">
                                            <label for="discount">Discount to apply</label>
                                            <div class="form-group input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-secondary" id="discount_unit">₦</span>
                                                </div>
                                                <input type="text" name="discount" required id="discount" class="form-control" value="{{ old('discount') }}" placeholder="100" onkeypress="return EzwashHelper.allowOnlyNum(event);" aria-describedby="discount_unit" maxlength="10">
                                            </div>
                                        </div>
                                        <br>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="offer_start_date">Offer Start Date</label>
                                                <input type="text" autocomplete="off" required data-min-date="{{ $minStartDate  }}" value="{{ old('start_date') }}" readonly name="start_date" id="offer_start_date" class="form-control offer_date_field" placeholder="Click to select date">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="offer_end_date">Offer End Date</label>
                                                <input type="text" autocomplete="off" required data-min-date="{{ $minStartDate  }}" value="{{ old('end_date') }}" readonly name="end_date" id="offer_end_date" class="form-control offer_date_field" placeholder="Click to select end date">
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-sm-12">
                                            <label for="offer_status">Set loyalty offer status</label>
                                            <div class="form-group" id="offer_status">
                                                <label class="radio-inline mr-1">
                                                    <input type="radio" name="status" required @if(old('status') == 'active') checked @endif id="active_status" value="active">Active
                                                </label>
                                                <label class="radio-inline mr-1">
                                                    <input type="radio" name="status" required @if(!old('status') || old('status') == 'inactive') checked @endif id="inactive_status" value="inactive">Inactive
                                                </label>
                                            </div>
                                            <input type="checkbox" hidden="hidden" name="force_active" id="force_active">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions text-right">
                                    <button type="submit" class="btn btn-primary col-md-2">
                                        <i class="la la-check-square-o"></i> Save
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
    </script>
    <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{asset('js/pages/add-loyalty-offer.js')}}" type="text/javascript"></script>
@endsection
