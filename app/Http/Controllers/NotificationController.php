<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $authUser = $this->getAuthUser();
        $notifications = $authUser->notifications()->orderBy('created_at', 'DESC')->get();
        return view('notification.list', compact('notifications'));
    }

    public function markAllAsRead(Request $request)
    {
        $authUser = $this->getAuthUser();
        $notifications = $authUser->notifications()->update([
            'status' => Notification::READ
        ]);
        return successResponse('Success', ['updated']);

    }


    /**
     * Update the specified notification in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        $authUser = $this->getAuthUser();
        $updated = $authUser->notifications()->find($notification->id)->update([
            'status' => Notification::READ,
        ]);
        return successResponse('Success', ['updated' => $updated]);
    }

    /**
     * Remove the specified notification from database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        //
    }
}
