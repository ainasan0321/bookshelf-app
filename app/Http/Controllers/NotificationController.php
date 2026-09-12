<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function index(Request $request) :view
    {
        $notifications = auth()->user()->notifications()->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id): RedirectResponse
    {
         $notification = auth()->user()->notifications()->findOrFail($id);

         $notification->markAsRead();

         return redirect()->back()->with('success', '通知を既読にしました。');
    }
}
