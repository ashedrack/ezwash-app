<?php
$queryParams = request()->all();
?>
@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <!-- Zero configuration table -->
    <section id="configuration">
        <div class="row">

            <div class="col-12">
                <div class="card">
                    <div class="card-header text-center page-title-row">
                        <h1 id="heading-icon-buttons" class="page-heading text-bold-500 pull-left">{{ $pageTitle }}</h1>
                        @if(auth()->user()->can('special_discount.create'))
                            <a href="{{ route('special_discount.create') }}" class="btn btn-outline-primary btn-md float-right"><em class="la la-plus" style="font-size: inherit;"></em> Add Special Offer</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-content collapse show">
                        {{ $loyaltyOffers->links() }}
                        <div class="card-body card-dashboard">
                            <table class="table table-striped table-bordered zero-configuration" id="loyaltyOffersTable">
                                <caption></caption>
                                <thead>
                                    <tr>
                                        @if(!$authUser->company_id)
                                        <th scope="col">Company</th>
                                        @endif
                                        <th scope="col">Loyalty Name</th>
                                        <th scope="col">Required Amount</th>
                                        <th scope="col">Discount to apply</th>
                                        <th scope="col">Start Date</th>
                                        <th scope="col">End Date</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($loyaltyOffers as $offer)
                                    <tr>
                                        @if(!$authUser->company_id)
                                            <td>{{ $offer->company->name }}</td>
                                        @endif
                                        <td>{{ $offer->display_name }}</td>
                                        <td>{{ $offer->discount_value }}</td>
                                        <td>{{ date('Y-m-d', strtotime($offer->start_date)) }}</td>
                                        <td>{{ date('Y-m-d', strtotime($offer->end_date)) }}</td>
                                        <td> @if($offer->status) <span class="text-primary">ACTIVE</span> @else <span class="text-secondary">INACTIVE</span> @endif </td>
                                        <td>
                                            <a class="btn btn-sm btn-primary round" href="{{ route('loyalty_offer.view', ['offer' => $offer->id]) }}">View</a>
                                            <a class="btn btn-sm btn-outline-primary round" href="{{ route('loyalty_offer.edit', ['offer' => $offer->id]) }}">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $loyaltyOffers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

