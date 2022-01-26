<div class="card">
    <div class="card-body">
        <form class="form" id="{{ $filter_id }}" action="{{ $filter_url }}" method="post">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="projectinput1">Company</label>
                            <select name="company" id="filter_company" class="form-control">
                                <option>Ezwash Main</option>
                                <option>Company 1</option>
                                <option>Company 2</option>
                                <option>Company 3</option>
                                <option>Company 4</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="projectinput1">Location</label>
                            <select name="company" id="filter_company" class="form-control">
                                <option>Yaba</option>
                                <option>Ikota</option>
                                <option>Lekki</option>
                                <option>Osapa</option>
                                <option>Ilupeju</option>
                                <option>Maryland</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="projectinput1">Role</label>
                            <select name="company" id="filter_company" class="form-control">
                                <option>Store Manager</option>
                                <option>Admin</option>
                                <option>SuperAdmin</option>
                                <option>Overall Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <button type="submit">{{ $button_text }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
