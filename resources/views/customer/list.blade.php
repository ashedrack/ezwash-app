@extends('layouts.app')

@section('title', 'All Customers')

@section('content')
    <!-- Zero configuration table -->
    <section id="configuration">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-center page-title-row">
                        <h1 id="heading-icon-buttons" class="page-heading text-bold-500 pull-left">All Customers</h1>
                        <a href="{{ route('customer.add') }}" class="btn btn-outline-primary btn-md float-right"><i class="la la-plus" style="font-size: inherit;"></i> Add Customer</a>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-content collapse show">
                        <div class="card-body card-dashboard">
                            <div class="card">
                                <div class="card-body">
                                    <form class="form" id="customers_filter" action="{{ route('customer.list') }}" method="get">
                                        <div class="form-body">
                                            @if($authUser->can('list_locations'))
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="filter_location">Location</label>
                                                        <select name="location" id="filter_location" class="form-control">
                                                            <option value="">Select Location</option>
                                                            @foreach($locations as $location)
                                                                <option value="{{ $location->id }}" {{ old('location',$queryParams['location'])? 'selected' : '' }}>{{ $location->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-9" style="clear: both;">
                                                    <div class="form-group">
                                                        <label for="filter_location">Enter search string</label>
                                                        <input class="form-control" value="{{ old('search_string', $queryParams['search_string']) }}" type="text" id="filter_name_or_email" name="search_string" placeholder="Enter Customer name, email or phone">
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                                <div class="row">
                                                    <div class="col-md-12" style="clear: both;">
                                                        <div class="form-group">
                                                            <label for="filter_location">Enter search string</label>
                                                            <input class="form-control" value="{{ old('search_string', $queryParams['search_string']) }}" type="text" id="filter_name_or_email" name="search_string" placeholder="Enter Customer name, email or phone">
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn btn-primary">Filter Customers</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>


                                    @if(!empty($queryParams))
                                        {{ $users->appends(['location' => $queryParams['location'], 'search_string' => $queryParams['search_string']])->links() }}
                                    @else
                                        {{ $users->links() }}
                                    @endif
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone Number</th>
                                                <th>Date Added</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($users as $user)
                                            <tr @if(!$user->_isActive()) class="inactive-record-row" @endif>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->phone }}</td>
                                                <td>{{ $user->created_at }}</td>
                                                <td class="text-truncate">
                                                    <a class="btn btn-sm btn-primary round" href="{{route('customer.view', ['customer' => $user->id])}}">View</a>
                                                    <a class="btn btn-sm btn-outline-secondary round" href="{{route('customer.edit', ['customer' => $user->id])}}">Edit</a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger round" onclick="EzwashHelper.deletionWarning('actionForm', '{{route('customer.delete',['customer' => $user->id])}}');">Delete</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No Customers Found !!</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    @if(!empty($queryParams))
                                        {{ $users->appends(['location' => $queryParams['location'], 'search_string' => $queryParams['search_string']])->links() }}
                                    @else
                                        {{ $users->links() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <form id="actionForm" method="post" style="display:none;">
        @csrf
    </form>
@endsection

@section('more-scripts')
@endsection

