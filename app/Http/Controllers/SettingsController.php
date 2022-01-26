<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $authUser = auth()->user();
        $locations = Location::allowedToAccess($authUser)->latest()->get();
        $companies = Company::latest()->get();
        $employees = Employee::getAllowed($authUser)->latest()->get();
        return view('settings.general', compact('employees', 'authUser', 'companies', 'locations'));
    }

    /**
     * Show the form for editing the settings details.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('settings.edit');
    }

    /**
     * Save the updated settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateReportRecipients(Request $request)
    {

        $this->validate($request, [
            'report_recipients' => 'required|array',
            'report_recipients.*.' => 'exists:employees,id'
        ]);
        $report_recipients = $request->report_recipients;
        Employee::whereIn('id', array_keys($report_recipients))->update([
            'receive_reports' => true
        ]);
        Employee::whereNotIn('id', array_keys($report_recipients))->update([
            'receive_reports' => false
        ]);
        return redirect(route('settings.general'))->with([
            'status' => 'success',
            'title' => 'OK',
            'message' => 'Report recipients updated successfully'
        ]);
    }

    public function generateUsers(Request $request)
    {
        $this->validate($request, [
//            'company' => ['nullable', 'numeric', 'exists:companies,id'],
//            'location' => ['nullable', 'numeric', 'exists:locations,id'],
            'last_activity_start_at' => ['nullable', 'date'],
            'last_activity_end_at' => ['nullable', 'date']
        ]);

        try{
            $authAdmin = auth()->user();
            $start_at = $request->last_activity_start_at;
            $end_at = $request->last_activity_end_at;
            $subject = "Customer Lists";
            $baseQuery = new User();

            if(!empty($start_at)){
                $baseQuery = (clone $baseQuery)->whereDate('created_at', '>=', $start_at);
            }
            if(!empty($end_at)){
                $baseQuery = (clone $baseQuery)->whereDate('created_at', '<=', $end_at);
            }

            $users = $baseQuery->orderBy('id', 'desc')->get(['name', 'email', 'phone', 'created_at']);

            if(!empty($users)){
                try{
                    sendReportMail($authAdmin, $users, $subject);
                    return back()->with(['status' => 'success', 'message' => 'Customers list of '.count($users).' records has been sent to your mail', 'title' => 'Ok']);;
                }catch(\Exception $e){
                    Log::error($e->getMessage());
                    return back()->with(['status' => 'error', 'message' => 'Failed to send customers list', 'title' => 'Oops!!']);
                }

            }
            return back()->with(['status' => 'success', 'message' => 'No customer found for the selected values', 'title' => 'Ok']);
        }catch(\Exception $e){
            return back()->with([
                'status' => 'error',
                'title' => 'Oops..',
                'message' => $e->getMessage()
            ]);
        }

    }
}
