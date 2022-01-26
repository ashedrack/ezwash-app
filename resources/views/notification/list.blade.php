@extends('layouts.app')

@section('title', 'All Notifications')

@section('navigation')
    @component('components.navigation.super_admin_navigation');
    @endcomponent
@endsection

@section('content')
    <!-- Zero configuration table -->
    <section id="configuration">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title view-title">All Notifications</h2>
                        <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body card-dashboard">
                            <table class="table table-striped table-bordered zero-configuration" id="ordersTable">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Title</th>
                                        <th>Content</th>
                                        <th>Time</th>
                                        <th>More Info</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if(count($notifications) > 0)
                                    @foreach($notifications as $notification)
                                    <tr>
                                        <td></td>
                                        <td>{{$notification->heading}}</td>
                                        <td>{{$notification->message}} </td>
                                        <td>{{$notification->created_at->diffForHumans()}}</td>
                                        <td>
                                            @if(!empty($notification->url))
                                                <a class="btn btn-sm btn-outline-info round" href="{{$notification->url}}">
                                                    View Order
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
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
    <script>
        $('#ordersTable').DataTable({
            searchable: true,
            order: [[ 0, "desc" ]],
            columnDefs: [
                {
                    "targets": [ 0 ],
                    "visible": false,
                    "searchable": false
                }
            ]
        });
    </script>
@endsection

