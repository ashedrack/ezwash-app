@extends('layouts.app')

@section('title', 'Company Profile')

@section('page-specific-styles')
    <style>
        #offerDetailsTable tr:first-child th, #offerDetailsTable tr:first-child td {
            border-top: 1px solid #e2ebf3;
        }
    </style>
@endsection

@section('content')
    <div class="content-header row">
    </div>
    <div class="content-body">
        <div class="row">
            <div class="col-lg-4" style="padding-bottom: 2em;">
                <div class="card d-flex align-items-center" style="height: 100%;">
                    <div class="nav-item dropdown" style="position: absolute;right: 5px;top: 5px;">
                        <a class="nav-link dropdown-toggle btn btn-outline-secondary" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Options</a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('loyalty_offer.edit', ['offer' => $offer->id]) }}">Edit</a>
                            {{--<div class="dropdown-divider"></div>--}}
                            {{--<a class="dropdown-item" onclick="EzwashHelper.deactivationWarning('actionForm', '{{ route('loyalty_offer.deactivate') }}', 'Any active offer will be deactivated on the start date');">Activate</a>--}}
                        </div>
                    </div>
                    <div class="card-content m-auto">
                        <table class="table table-responsive" id="offerDetailsTable">
                            @if($authUser->can('list_companies'))
                                <tr>
                                    <th class="border-right-black">Company</th>
                                    <td>{{ $offer->company->name }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th class="border-right-black">Offer Name</th>
                                <td>{{ $offer->display_name }}</td>
                            </tr>
                            <tr>
                                <th class="border-right-black">Description</th>
                                <td>Get <span class="naira-prefix text-bold-500">{{ $offer->discount_value }}</span> for every <span class="naira-prefix text-bold-500">{{ $offer->spending_requirement }}</span> spent</td>
                            </tr>
                            <tr>
                                <th class="border-right-black">Status</th>
                                <td class="text-capitalize text-success">@if($offer->status) <span class="text-primary">ACTIVE</span> @else <span class="text-danger">INACTIVE</span> @endif</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card profile-stats-box stats-box border-left-5 border-left-danger">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-center">
                                            <p class="stat-value">{{ $offer->appliedDiscounts()->count() }}</p>
                                            <p class="stat-title">Total Applied</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card profile-stats-box stats-box border-left-5 border-left-primary">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-center">
                                            <p class="stat-value naira-prefix">{{ $offer->unusedDiscounts()->sum('discount_earned') }}</p>
                                            <p class="stat-title">Users with discount({{ $offer->unusedDiscounts()->count() }})</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card profile-stats-box stats-box border-left-5 border-left-primary">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-center">
                                            <p class="stat-value">0</p>
                                            <p class="stat-title">Previously Enjoyed</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card profile-stats-box stats-box border-left-5 border-left-danger">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-center">
                                            <p class="stat-value naira-prefix">{{ $offer->appliedDiscounts()->sum('discount_earned') }}</p>
                                            <p class="stat-title">Total Amount Applied</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Revenue, Hit Rate & Deals -->
        <div class="row">
            <div class="col-12">
                <div class="card box-shadow-0">
                    <div class="card-header">
                        <h2 class="card-title">Customers with unused discount</h2>
                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body card-dashboard">
                            <table class="table table-bordered" id="offer-customers">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Amount Spent</th>
                                    <th>Discount Earned</th>
                                    <th>Date Added</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($unusedDiscounts as $discount)
                                <tr>
                                    <td>{{ $discount->user->name }}</td>
                                    <td>{{ $discount->user->email }}</td>
                                    <td class="naira-prefix">{{ $discount->amount_spent }}</td>
                                    <td class="naira-prefix">{{ $discount->discount_earned }}</td>
                                    <td>{{ $discount->created_at->format('Y-m-d') }}</td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No unused discounts for this offer</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('more-scripts')
    <script>
        $('.customer_orders').DataTable({
            searchable: false
        });
        $('#offer-customers').DataTable({
            searchable: false
        });
    </script>
@endsection
