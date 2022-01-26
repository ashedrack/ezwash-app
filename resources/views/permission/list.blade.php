@extends('layouts.app')

@section('title', 'All Permissions')

@section('content')
    <!-- Zero configuration table -->
    <section id="configuration">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header text-center page-title-row">
                        <h1 id="heading-icon-buttons" class="text-bold-500 pull-left">All Permission</h1>
                        <button type="button" class="btn btn-primary btn-md float-md-right" onclick="$('#add_permission_section').show()" id="addPermissionBtn"><i class="la la-plus" style="font-size: inherit;"></i> Add Permission</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <section class="card" id="add_permission_section" style="display: none">
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
                        <form class="form" id="add_permission_form" action="{{ route('permission.save') }}" method="post">
                            @csrf
                            <div class="form-body col-6">
                                <h4 class="card-title">Add a permission</h4>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="label">Group</label>
                                            <select class="form-control" required id="group" name="group">
                                                <option value="">Select a group</option>
                                                @foreach($permissionGroups as $group )
                                                    <option value="{{ $group->id }}" {{ (old('group') == $group->id)? 'selected': '' }}>{{ $group->display_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="label">Name</label>
                                            <input class="form-control" type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Please do not add any spaces, you may use underscore(_) as a separator" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="label">Display Name</label>
                                            <input class="form-control" type="text" id="display_name" value="{{ old('display_name') }}" name="display_name" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="label">Description</label>
                                            <input class="form-control" type="text" id="description" value="{{ old('description') }}" name="description" placeholder="Briefly describe this permission" required>
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
                                            <button type="button" class="btn btn-danger w-100" onclick="$('#add_service_section').hide(); $('#add_service_btn').show();">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>
                <div class="card">
                    <div class="card-content collapse show">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="display-inline-block">
                                    @foreach ($errors->all() as $error)
                                        <li class="text-white">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="card-body card-dashboard">
                            <form action="{{ route('permission.update') }}" method="post" enctype="multipart/form-data" id="updatePermission">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-bordered table-fixed" id="permissionsTable">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Permission Name</th>
                                                @foreach($roles as $role)
                                                    <th>{{ $role->display_name }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php $n = 0; ?>
                                        @forelse($permissionGroups as $group )
                                            @if($n !== 0)
                                                <tr style="height: 20px"></tr>
                                            @endif
                                            <tr class="heading-row">
                                                <td colspan="2" class="text-uppercase"><?= (!is_null($group->display_name)) ? $group->display_name : $group->name ?></td>
                                                @foreach($roles as $role)
                                                    <td></td>
                                                @endforeach
                                            </tr>
                                            <?php $permissions = $group->permissions()->get(); ?>
                                            @if(!empty($permissions))
                                                @foreach($permissions as $perm)
                                                    <tr>
                                                        <td>{{ $n += 1 }}</td>
                                                        <td>{{ $perm->display_name }}</td>
                                                        @foreach($roles as $role)
                                                            <td>
                                                                @if($role->hasPermission($perm->name))
                                                                    <input type="checkbox" checked name="roles[{{$role->id}}][permissions][{{$perm->id}}]">
                                                                @else
                                                                    <input type="checkbox" name="roles[{{$role->id}}][permissions][{{$perm->id}}]">
                                                            </td>
                                                            @endif
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @empty

                                        @endforelse
                                        @if($unGroupedPermissions->count() > 0)
                                            @if($n !== 0)
                                                <tr style="height: 20px"></tr>
                                            @endif
                                            <tr class="heading-row">
                                                <td colspan="2" class="text-uppercase">UNGROUPED PERMISSIONS</td>
                                                @foreach($roles as $role)
                                                    <td></td>
                                                @endforeach
                                            </tr>
                                            @foreach($unGroupedPermissions as $perm)
                                            <tr>
                                                <td>{{ $n += 1 }}</td>
                                                <td>{{ $perm->display_name }}</td>
                                                @foreach($roles as $role)
                                                    <td>
                                                    @if($role->hasPermission($perm->name))
                                                        <input type="checkbox" checked name="roles[{{$role->id}}][permissions][{{$perm->id}}]">
                                                    @else
                                                        <input type="checkbox" name="roles[{{$role->id}}][permissions][{{$perm->id}}]">
                                                    </td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                    @if(auth()->user()->can('edit_permission'))
                                    <div class="col-12 text-center">
                                        <button class="btn btn-primary col-md-3" type="submit">Update Permissions</button>
                                    </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
