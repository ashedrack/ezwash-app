<?php

namespace App\Http\Controllers;

use App\Classes\Meta;
use App\Classes\StatisticsFilters;
use App\Models\Order;
use App\Models\OrderRequest;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected $roles_nav_menu = [
        'super_admin' => 'components.navigation.super_admin_navigation',
        'admin' => 'components.navigation.admin_navigation',
        'store_manager' => 'components.navigation.store_manager_navigation'
    ];

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function home(Request $request)
    {
        $title = 'Dashboard';
        $authUser = $this->getAuthUser();
        $statisticsResult = (new StatisticsFilters($request, $authUser))->dashboardStatistics();
        $pickupDeliveryStats = $statisticsResult->pickupDeliveryStats;
        $topCustomers = $statisticsResult->top_customers;
        return view('home', compact('title', 'topCustomers', 'statisticsResult', 'pickupDeliveryStats'));
    }

    public function revenueChart(Request $request)
    {
        try {
            $authUser = $this->getAuthUser();
            $statisticsResult = (new StatisticsFilters($request, $authUser))->dashboardStatistics();
            $responseData = '';
            return successResponse('Customer created successful', $responseData, $request);

        } catch (\Exception $e){
            return errorResponse('Something went wrong', 500, null, $e);
        }
    }

    public function profile(){
        $employee = Auth::user();
        $employeeActivities = $employee->activities()->orderBy('created_at', 'desc')->simplePaginate(10, ['*'], 'activities_page');
        return view('admin.profile', compact('employee', 'employeeActivities'));
    }

    public function allPermissions(){
        developerOnlyAccess();
        $roles = Role::all();
        $permissionGroups = PermissionGroup::all();
        $unGroupedPermissions = Permission::whereNull('group_id')->get();
        return view('permission.list', compact('roles', 'permissionGroups', 'unGroupedPermissions'));
    }

    public function updatePermission(Request $request){
        developerOnlyAccess();
        $this->validate($request, [
            'roles' => 'required|array',
            'roles.*.' => 'exists:roles,id',
            'roles.*.permissions.*.' => 'exists:permissions,id'
        ]);
        $roles = $request->roles;
        foreach ($roles as $role_id => $r){
            $role = Role::find($role_id);
            $selectedPermissions = $r['permissions'];
            foreach ($selectedPermissions as $p_id => $p){
                if($p === 'on'){
                    $permission = Permission::find($p_id);
                    if(!$role->hasPermission($permission->name)){
                        $role->attachPermission($permission);
                    }
                }
            }
            RolePermission::where('role_id', $role_id)->whereNotIn('permission_id', array_keys($selectedPermissions))->delete();
        }
        return redirect()->back();
    }

    public function savePermission(Request $request){
        developerOnlyAccess();
        $this->validate($request, [
            'group' => 'required|exists:permission_groups,id',
            'name' => 'required|string|unique:permissions,name',
            'display_name' => 'required|string|unique:permissions,display_name',
            'description' => 'required'
        ]);
        $permission = Permission::create([
            'group_id' => $request->group,
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
        ]);
        $dev_role = Role::where('name', 'app_developer')->first();
        if($dev_role){
            $dev_role->attachPermission($permission);
        }
        return redirect()->route('permission.list')->with([
            'status' => 'success',
            'title' => 'OK',
            'message' => 'Permission added successfully'
        ]);
    }


}
