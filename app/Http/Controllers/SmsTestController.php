<?php

namespace App\Http\Controllers;

use App\Services\SemaphoreSmsService;
use Illuminate\Http\Request;

class SmsTestController extends Controller
{
    public function send(Request $request, SemaphoreSmsService $sms)
    {
        $data = $request->validate([
            'number' => 'required|string',
            'message' => 'required|string|max:500',
        ]);

        $log = $sms->send(
            number: $data['number'],
            message: $data['message'],
            meta: ['event' => 'manual_test']
        );

        return response()->json([
            'success' => in_array($log->status, ['queued', 'pending', 'sent'], true),
            'status' => $log->status,
            'error_message' => $log->error_message,
            'log' => $log,
        ]);
    }
}