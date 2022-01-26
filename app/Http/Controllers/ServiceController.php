<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:create_service', ['only' => ['create', 'save']]);
        $this->middleware('permission:edit_service', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_service', ['only' => ['delete']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request)
    {
        $query_name = null;
        if($request->has('name')){
            $this->validate($request, [
                'name' => 'required'
            ]);
            $services = Service::allowedToAccess()->where('name', 'LIKE', "%$request->name%")->orderBy('name', 'asc')->paginate(20);
            $query_name = $request->name;
        }else {
            $services = Service::allowedToAccess()->orderBy('name', 'asc')->paginate(20);
        }
        return view('service.list', compact('services', 'query_name'));
    }

    /**
     * Save new services in the database
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function save(Request $request)
    {
        $companyID = $this->getAuthUser()->company_id ?? $request->company_id;
        $this->validate($request, [
            'company_id' => 'nullable|exists:companies,id',
            'name' => ['required', Rule::unique('services', 'name')->where('company_id', $companyID)],
            'price' => 'required|numeric|max:500000'
        ]);
        $service = Service::create([
            'name' => $request->name,
            'price' => $request->price,
            'company_id' => $companyID
        ]);
        return redirect()->route('service.list')->with(['status' => 'success','title' => 'OK', 'message' => "$service->name added successfully"]);
    }

    /**
     * Update the specified service
     *
     * @param Request $request
     * @param Service $service
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Service $service)
    {
        $companyID = $service->company_id;
        $this->validate($request, [
            'name' => ['required', Rule::unique('services', 'name')->where('company_id', $companyID)->ignore($service->id)],
            'price' => 'required|numeric|max:500000'
        ]);
        $service->update([
            'name' => $request->name,
            'price' => $request->price,
        ]);
        return redirect()->route('service.list')->with(['status' => 'success','title' => 'OK', 'message' => "$service->name updated successfully"]);
    }

    /**
     * Delete a specified service (cannot be undone)
     *
     * @param Service $service
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function delete(Service $service)
    {
        $service->delete();
        return redirect(route('service.list'))->with(['status' => 'success','title' => 'OK', 'message' => "Product/Service deleted successfully"]);
    }
}
