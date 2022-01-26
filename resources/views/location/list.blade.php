@extends('layouts.app')

@section('title', 'All Locations')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header text-center page-title-row">
                    <h1 id="heading-icon-buttons" class="page-heading text-bold-500 pull-left">All Locations</h1>
                    @if($authUser->can('create_location'))
                        <a href="{{ route('location.add') }}" class="btn btn-outline-primary btn-md float-right"><em class="la la-plus" style="font-size: inherit;"></em> Add Location</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-content collapse show">
                    <div class="card-body card-dashboard">
                        {{ $locations->links() }}
                        <table class="table table-striped table-bordered zero-configuration" id="locations_table">
                            <caption></caption>
                            <thead>
                                <tr>
                                    @if($companies)
                                    <th scope="col">Company</th>
                                    @endif
                                    <th scope="col">Name</th>
                                    <th scope="col">Contact Phone</th>
                                    <th scope="col">Address</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($locations as $location)
                                <tr @if(!$location->_isActive()) class="inactive-record-row" @endif>
                                    @if($companies)
                                    <td>{{ $location->company->name }}</td>
                                    @endif
                                    <td>{{ $location->name }}</td>
                                    <td>{{ $location->phone }}</td>
                                    <td>{{ $location->address }}</td>
                                    <td class="text-truncate">
                                        <a href="{{route('location.view', ['location' => $location->id])}}" class="btn btn-primary btn-sm"> View </a>
                                        <a href="{{ route('location.edit', ['location' => $location->id]) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                                        <button type="button" data-deletion-prompt data-deletion-form="actionForm" data-deletion-url="{{ route('location.delete',['location' => $location->id]) }}" class="btn btn-outline-danger btn-sm"> Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"> No Locations Found</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        {{ $locations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form method="post" id="actionForm" style="display: none;">
        @csrf
        <input type="hidden" id="deletion_type" name="deletion_type">
    </form>
    @component('components.deletion-prompt-template', [
        'permanentDeletionWarning' => 'PLEASE NOTE THAT THIS WILL DELETE THE LOCATION, ALL EMPLOYEES, AND ORDERS ASSOCIATED WITH IT FROM THE DATABASE'
    ])
    @endcomponent
@endsection

@section('more-scripts')
    <script src="{{ asset('js/deletion_helper.js') }}"></script>
    <script src="{{ asset('js/pages/list-locations.js') }}"></script>
@endsection
