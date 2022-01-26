<?php

namespace App\Observers;

use App\Models\Location;
use App\Models\Locker;

class LocationObserver
{
    /**
     * Handle the location "created" event.
     *
     * @param  \App\Models\Location  $location
     * @return void
     */
    public function created(Location $location)
    {
        //Create Locker entries from the number of lockers set in the location
        if($location->number_of_lockers <= 0){
            return;
        }

        $lockersRange = range(0, $location->number_of_lockers);
        $lockers = array_map(function ($locker) use ($location){
            return [
                'location_id' => $location->id,
                'locker_number' => $locker
            ];
        }, $lockersRange);
        Locker::insert($lockers);
        if(!empty(auth()->user())){
            auth()->user()->recordActivity([
                [
                    'name' => 'created_location',
                    'url' => route('location.view', ['location' => $location->id]),
                    'description' => 'Added a location: ' . $location->name
                ]
            ]);
        }
    }

    /**
     * Handle the location "updated" event.
     *
     * @param  \App\Models\Location  $location
     * @return void
     */
    public function updated(Location $location)
    {
        if($location->isDirty('number_of_lockers')) {
            $lockersRange = range(0, $location->number_of_lockers);
            //Delete unoccupied lockers not in the new range
            Locker::whereNotIn('locker_number', $lockersRange)->where('occupied', 0)->delete();

            $currentLockers = $location->lockers()->get()->pluck('locker_number')->toArray();

            //Only insert lockers that are not currently on the table;
            $lockersToInsert = (count($currentLockers) > 0)? array_diff($lockersRange, $currentLockers) : $lockersRange;
            $lockers = array_map(function ($locker) use ($location) {
                return [
                    'location_id' => $location->id,
                    'locker_number' => $locker
                ];
            }, $lockersToInsert);
            Locker::insert($lockers);
        }
        if(!empty(auth()->user())){
            auth()->user()->recordActivity([
                [
                    'name' => 'updated_location',
                    'url' => route('location.view', ['location' => $location->id]),
                    'description' => 'Updated a location: ' . $location->name
                ]
            ]);
        }
    }

    /**
     * Handle the location "deleted" event.
     *
     * @param  \App\Models\Location  $location
     * @return void
     */
    public function deleted(Location $location)
    {
        if(!empty(auth()->user())) {
            auth()->user()->recordActivity([
                [
                    'name' => 'deleted_a_location',
                    //  'url' => route('location.view', ['location' => $location->id]),
                    'description' => 'Deleted a location: ' . $location->name
                ]
            ]);
        }
    }

    /**
     * Handle the location "restored" event.
     *
     * @param  \App\Models\Location  $location
     * @return void
     */
    public function restored(Location $location)
    {
        $location->lockers()->restore();
        if(!empty(auth()->user())){
            auth()->user()->recordActivity([
                [
                    'name' => 'location_restored',
                    'url' => route('location.view', ['location' => $location->id]),
                    'description' => 'Restored a previously deleted location: ' . $location->name
                ]
            ]);
        }
    }

    /**
     * Handle the location "force deleted" event.
     *
     * @param  \App\Models\Location  $location
     * @return void
     */
    public function forceDeleted(Location $location)
    {
        if(!empty(auth()->user())){
            auth()->user()->recordActivity([
                [
                    'name' => 'permanently_delete_location',
//                'url' => route('location.view', ['location' => $location->id]),
                    'description' => 'Deleted a location permanently: ' . $location->name
                ]
            ]);
        }
    }
}
