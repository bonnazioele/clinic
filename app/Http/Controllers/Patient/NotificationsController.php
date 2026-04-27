<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;

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

        return view('notifications.index', compact('all','unread'));
    }

    public function markAllRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    }
}
