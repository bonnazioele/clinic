<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the list of notifications, newest first.
     */
    public function index()
    {
        $user     = Auth::user();
        $all      = $user->notifications()->latest()->paginate(20);
        $unread   = $user->unreadNotifications()->count();

        return view('notifications.index', compact('all','unread'));
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    }
}
