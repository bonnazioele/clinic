<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsMessageLog extends Model
{
    protected $fillable = [
        'smsable_type',
        'smsable_id',
        'notifiable_type',
        'notifiable_id',
        'provider',
        'recipient_number',
        'sender_name',
        'message',
        'status',
        'provider_message_id',
        'provider_response',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'provider_response' => 'array',
        'sent_at' => 'datetime',
    ];

    public function smsable()
    {
        return $this->morphTo();
    }

    public function notifiable()
    {
        return $this->morphTo();
    }
}