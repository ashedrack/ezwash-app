<?php

namespace App\Http\Controllers;

use App\Classes\StatisticsFilters;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $authUser = $this->getAuthUser();
        $request->merge([
            'start_date' => $request->start_date ?? now()->toDateString(),
            'end_date' => $request->end_date ?? now()->toDateString()
        ]);
        $statsFilter = new StatisticsFilters($request, $authUser);

        $stats = $statsFilter->generalStatistics([]);
        return view('statistics.view', compact('stats', 'authUser'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function filter(Request $request)
    {
        try {
            $company = $request->company;
            $location = $request->location;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            if (!empty($start_date)) {
                $start_date = Carbon::parse($start_date)->toDateString();
            }
            if (!empty($end_date)) {
                $end_date = Carbon::parse($end_date)->toDateString();
            }
            $authUser = $this->getAuthUser();
            $statsFilter = new StatisticsFilters($request, $authUser);
            $stats = $statsFilter->generalStatistics([
                'company' => $company,
                'location' => $location,
                'startDate' => $start_date,
                'endDate' => $end_date
            ]);

            return response()->json($stats, 200);
        } catch (\Exception $e){
            logCriticalError('Error getting filtered statistics', $e);
            return response()->json()->setStatusCode('500');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
