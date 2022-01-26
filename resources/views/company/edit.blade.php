@extends('layouts.app')

@section('title', 'Edit Company')

@section('content')
    <section id="basic-form-layouts">
        <div class="row match-height justify-content-md-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" id="basic-layout-form">Edit Company</h4>

                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert bg-danger">
                                    <ul class="display-inline-block">
                                        @foreach ($errors->all() as $error)
                                            <li class="text-white">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form class="form" action="{{ route('company.update', ['company' => $company->id])}}" method="post">
                                @csrf
                                <div class="form-body">
                                    <h4 class="form-section"><em class="la la-paperclip"></em> Company Details</h4>
                                    <div class="form-group">
                                        <label for="companyName">Company</label>
                                        <input type="text" name="company_name" id="companyName" class="form-control" placeholder="Company Name"
                                               value="<?= (old('company_name')) ? old('company_name'): $company->name ?>">
                                    </div>
                                    <h4 class="form-section"><em class="ft-user"></em> Owner Info</h4>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="projectinput1">Full Name</label>
                                                <input type="text" name="owner_name" id="owner-name" class="form-control" placeholder="Enter first and last name"
                                                       value="<?= (old('owner_name')) ? old('owner_name'): $company->owner ? $company->owner->name : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="projectinput3">E-mail</label>
                                                <input type="text" name="email" id="owner-email" class="form-control" placeholder="E-mail" value="<?= (old('owner_email')) ? old('owner_email'): $company->owner->email ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="projectinput4">Phone Number</label>
                                                <input type="text" name="phone" id="owner-phone" class="form-control" placeholder="Phone" value="<?= (old('owner_phone')) ? old('owner_phone'): $company->owner->phone ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions text-right">
                                    <button type="submit" class="btn btn-primary col-md-2">
                                        <em class="la la-check-square-o"></em> Update
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
