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

    public function index()
    {
        $user     = Auth::user();
        $all      = $user->notifications()->latest()->paginate(20);
        $unread   = $user->unreadNotifications()->count();

        // Auto-mark all notifications as read when user views the page
        $user->unreadNotifications->markAsRead();

        return view('notifications.index', compact('all','unread'));
    }

    public function markAllRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    }
}
