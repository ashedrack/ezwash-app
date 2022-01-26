@extends('layouts.app')

@section('title', 'All Services')
@section('content')
    <!-- Zero configuration table -->
    <section id="configuration">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-center page-title-row">
                        <h1 id="heading-icon-buttons" class="page-heading text-bold-500 pull-left">Products and Services</h1>
                        @if(auth()->user()->can('create_service'))
                            <button id="add_service_btn" onclick="$('#add_service_section').show();" class="btn btn-outline-primary btn-md float-right"><i class="la la-plus" style="font-size: inherit;"></i> Add A Product/Service</button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-content collapse show">
                        <div class="card-body card-dashboard">
                            <section class="card" id="add_service_section" style="display: none">
                                @if ($errors->any())
                                    <div class="alert bg-danger">
                                        <ul class="display-inline-block">
                                            @foreach ($errors->all() as $error)
                                                <li class="text-white">{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <form class="form" id="add_service_form" action="{{ route('service.save') }}" method="post">
                                        @csrf
                                        <div class="form-body col-6">
                                            <h4 class="card-title">Add a product/service</h4>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="label">Name</label>
                                                        <input class="form-control" type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Enter the product/service name" required>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="label">Price(N)</label>
                                                        <input class="form-control" type="number" min="10" maxlength="6" value="{{ old('price') }}" id="price" name="price" placeholder="How much does this cost?" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn btn-primary w-100">Save</button>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <button type="button" class="btn btn-danger w-100" onclick="$('#add_service_section').hide();">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </section>
                            <form class="form-row" method="get" action="{{ route('service.list') }}">
                                <div class="col-12">
                                    <div class="form-group width-400 float-right">
                                        <a href="{{ route('service.list') }}" class="btn width-20-per float-left underline">CLEAR</a>
                                        <input class="form-control width-60-per float-left" required value="{{ old('name', $query_name) }}" type="text" min="10" maxlength="6" id="search_query" name="name" placeholder="Enter service name">
                                        <button type="submit" class="btn btn-primary width-20-per float-left">Filter</button>
                                    </div>
                                </div>
                            </form>
                            @if(!is_null($query_name))
                                {{ $services->appends(['name' => $query_name])->links() }}
                            @else
                                {{ $services->links() }}
                            @endif
                            <table class="table table-striped table-bordered zero-configuration" id="services_table">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Usage Count</th>
                                    <th>Date Added</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($services as $service)
                                    <tr>
                                        <td>{{ $service->name }}</td>
                                        <td>N{{ $service->price }}</td>
                                        <td>{{ $service->usage() }}</td>
                                        <td>{{ $service->created_at }}</td>
                                        <td class="text-truncate">
                                            <a class="btn btn-sm btn-outline-primary round"  onclick="toggle_service({name: '{{$service->name}}', price: Number('{{$service->price}}'), service_id: '{{ $service->id}}', action: '{{ route('service.update', ['service' => $service->id]) }}'});">Edit</a>
                                            <button type="button" class="btn btn-sm btn-outline-danger round" onclick="EzwashHelper.deletionWarning('actionForm', '{{route('service.delete',['service' => $service->id])}}');">Delete</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center"> No Services Found</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                            @if(!is_null($query_name))
                                {{ $services->appends(['name' => $query_name])->links() }}
                            @else
                                {{ $services->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <form id="actionForm" method="post" style="display:none;">
        @csrf
    </form>
    <template id="editServiceTemplate">
        <form class="form" id="edit_service_form" method="post">
            @csrf
            @if (old('action_type') == 'edit' && $errors->any())
                <div class="alert bg-danger">
                    <ul class="display-inline-block">
                        @foreach ($errors->all() as $error)
                            <li class="text-white">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="form-body text-left">
                <h4 class="card-title text-dark">Edit product/service</h4>
                <div class="row">
                    <input name="action_type" value="edit" type="hidden">
                    <input name="service_id" id="service_id" value="" type="hidden">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="label">Name</label>
                            <input class="form-control" type="text" id="edit_name" required name="name" placeholder="Enter the product/service name">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="label">Price(N)</label>
                            <input class="form-control" type="number" id="edit_price" required name="price" min="10" maxlength="6" placeholder="How much does this cost?">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary w-100">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </template>
@endsection

@section('more-scripts')
    <script src="{{ asset('js/pages/list-services.js') }}"></script>
    <script>
        $(document).ready(function () {
            @if(old('action_type') == 'edit')
                <?php $serviceID =  old('service_id'); ?>
                const serviceID = '{{ $serviceID }}';

                toggle_service({
                    name: "{{ old('name')  }}",
                    price: "{{ old('price')  }}",
                    service_id: serviceID,
                    action: "{{ route('service.update', ['service' => $serviceID ]) }}"
                });
            @endif
        });
    </script>
@endsection

