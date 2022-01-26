@extends('layouts.app')

@section('title', 'All Companies')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header text-center page-title-row">
                    <h1 id="heading-icon-buttons" class="page-heading text-bold-500 pull-left">Registered Companies</h1>
                    @if(auth()->user()->can('create_company'))
                    <a href="{{ route('company.add') }}" class="btn btn-outline-primary btn-md float-right"><i class="la la-plus" style="font-size: inherit;"></i> Add Company</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div id="recent-transactions" class="col-12">
            <div class="card p-1">
                <div class="card-content">
                    <div class="table-responsive">
                        <table id="allCompanies" class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th class="border-top-0">Company Name</th>
                                    <th class="border-top-0">Owner</th>
                                    <th class="border-top-0">Locations</th>
                                    <th class="border-top-0">Employees</th>
                                    <th class="border-top-0">Date</th>
                                    <th class="border-top-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($companies as $company)
                                <tr>
                                    <td class="text-truncate">{{ $company->name }}</td>
                                    <td class="text-truncate">{{ $company->owner ? $company->owner->name: '' }}</td>
                                    <td class="text-truncate">{{ $company->locations()->count() }}</td>
                                    <td class="text-truncate">{{ $company->employees()->count() }}</td>
                                    <td class="text-truncate">{{ $company->created_at }}</td>
                                    <td class="text-truncate">
                                        <a class="btn btn-sm btn-primary round" href="{{ route('company.view', ['company' => $company->id]) }}">View</a>
                                        <a class="btn btn-sm btn-outline-secondary round" href="{{ route('company.edit', ['company' => $company->id]) }}">Edit</a>
                                        <button onclick="companyDelete('actionForm', '{{ route('company.delete', ['company' => $company->id]) }}')" class="btn btn-sm btn-outline-danger round">Delete</button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="deleteAlertContent" style="display: none;">
        <div class="row" id="deletePrompt">
            <div class="container">
                <button type="button" class="btn btn-danger float-left width-150" onclick="swal.setActionValue({ confirm: 'temporary' }); swal.close();">Delete</button>
                <a href="javascript:void(0);" class="btn btn-outline-danger float-right width-150" onclick="$('#deleteAlertDiv #deletePrompt').hide(); $('#deleteAlertDiv #permanent-implication').show()">Completely Delete</a>
            </div>
        </div>
        <div class="row" id="permanent-implication" style="display: none; text-align: left">
            <p>PLEASE NOTE THAT THIS WILL DELETE THE COMPANY FROM THE DATABASE, ALL EMPLOYEES, AND ORDERS ASSOCIATED WITH THIS COMPANY</p>
            <p>This action is NOT reversible. Do you agree?</p>
            <div class="container">
                <a href="javascript:void(0);" onclick="$('#deleteAlertDiv #permanent-implication').hide(); $('#deleteAlertDiv #deletePrompt').show();" class="float-left"><< No</a>
                <a href="javascript:void(0);" class="float-right text-danger" onclick="swal.setActionValue({ confirm: 'permanent' }); swal.close();">Yes, Continue</a>
            </div>
        </div>
    </div>
    <form id="actionForm" method="post" style="display:none;">
        @csrf
        <input type="hidden" name="deletion_type" id="deletion_type">
    </form>
@endsection
@section('more-scripts')
    <script src="{{ asset('js/pages/list-companies.js') }}"></script>
@endsection

