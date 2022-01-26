    <div class="card-body position-relative">
        @if ($errors->any())
            <div class="alert bg-danger">
                <ul class="display-inline-block">
                    @foreach ($errors->all() as $error)
                        <li class="text-white">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <section class="col-md-12 order-customer">
            <table class="table table-responsive">
                <tr>
                    <th class="border-right-black">Customer</th>
                    <td class="text-warning">{{ $customer->name }}</td>
                </tr>
                <tr>
                    <th class="border-right-black">Location</th>
                    @if($authUser->can('list_locations'))
                        <td>
                            <select name="location" id="order_location" class="form-control" style="height: auto;">
                                <option value="">Select a location</option>
                                @foreach($locations as $location)
                                    @if(old('location') == $location->id)
                                        <option value="{{ $location->id }}" selected>{{ $location->name }}</option>
                                    @else
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </td>
                    @else
                        <td class="text-warning">{{ $authUser->location->name }}</td>
                    @endif
                </tr>
                <tr>
                    <th class="border-right-black">Order Type</th>
                    <td class="text-warning">Self Service</td>
                </tr>
            </table>
        </section>
        <section class="col-md-12 services-dropdown-wrapper">
            <div class="services-dropdown">
                {{ $servicesDropdown }}
            </div>
        </section>
    </div>
    <div class="card-body">
        {{ $orderServices }}
    </div>
    <div class="card-body">
        {{ $orderForm }}
    </div>
