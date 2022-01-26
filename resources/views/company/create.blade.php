@extends('layouts.app')

@section('title', 'Add Company')

@section('content')
    <section id="basic-form-layouts">
        <div class="row match-height justify-content-md-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" id="basic-layout-form">Add Company</h4>

                        @if ($errors->any())
                            <div class="alert bg-danger">
                                <ul class="display-inline-block">
                                    @foreach ($errors->all() as $error)
                                        <li class="text-white">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body">
                            <form class="form" method="post" id="createCompany" action="{{ route('company.save') }}">
                                @csrf
                                <div class="form-body">
                                    <h4 class="form-section"><i class="la la-paperclip"></i> Company Details</h4>
                                    <div class="form-group">
                                        <label for="companyName">Company</label>
                                        <input type="text" name="company_name" value="{{ old('company_name') }}" required data-rule-minlength="3" data-rule-maxlength="100" id="companyName" class="form-control" placeholder="Company Name">
                                    </div>
                                    <h4 class="form-section"><i class="ft-user"></i> Owner Info</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="projectinput1">First Name</label>
                                                <input type="text" name="first_name" value="{{ old('first_name') }}" required data-rule-minlength="3" data-rule-maxlength="100" maxlength="100" id="owner-first-name" class="form-control" placeholder="First Name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="projectinput2">Last Name</label>
                                                <input type="text" name="last_name" value="{{ old('last_name') }}" required data-rule-minlength="3" data-rule-maxlength="100" maxlength="100" id="owner-first-name" class="form-control" placeholder="Last Name">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="projectinput3">E-mail</label>
                                                <input type="email" name="email" value="{{ old('email') }}" required data-rule-minlength="3" data-rule-maxlength="100" maxlength="100" id="owner-email" class="form-control" placeholder="E-mail">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="projectinput4">Phone Number</label>
                                                <input type="text" name="phone" value="{{ old('phone') }}" onkeypress="return allowOnlyNum(event);" data-rule-validphone required maxlength="11"  id="owner-phone" class="form-control" placeholder="Phone">
                                            </div>
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
        (function($) {
            $('#createCompany').validate();
        })(jQuery);
    </script>
@endsection
