<div class="card p-2">
    <div class="card-header">
        <h2 class="card-title">Recent Activities</h2>
    </div>
    <div class="card-content">
        <section class="paginator-wrapper float-right">
            {{ $allActivities->links('vendor.pagination.simple-bootstrap-4') }}
        </section>
        <table id="recentActivities" class="table table-bordered table-xl">
            <thead>
            <tr>
                <th class="border-top-0">Title</th>
                <th class="border-top-0">Description</th>
                <th class="border-top-0">Date</th>
                @if(!isset($is_member_activities))
                    <th class="border-top-0">Action</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($allActivities as $activity)
                <tr>
                    <td class="text-truncate">{{ ucwords(str_replace('_', ' ', $activity->activity_type->name)) }}</td>
                    <td class="text-truncate">{{ $activity->description }}</td>
                    <td class="text-truncate">{{ date('F jS, Y', strtotime($activity->created_at)) }}</td>
                    @if(!isset($is_member_activities))
                        <td>@if($activity->url) <a href="{{$activity->url}}" class="btn btn-outline-primary">View</a> @endif</td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
        <section class="paginator-wrapper float-right">
            {{ $allActivities->links('vendor.pagination.simple-bootstrap-4') }}
        </section>
    </div>
</div>
