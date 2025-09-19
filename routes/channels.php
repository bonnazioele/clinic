<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('clinic.queue.{clinicId}', function ($user, $clinicId) {
    return $user->is_admin ||
        ($user->is_secretary && $user->secretaryClinics()->where('clinics.id',$clinicId)->exists()) ||
        ($user->is_doctor && $user->clinics()->where('clinics.id',$clinicId)->exists());
});

Broadcast::channel('user.notifications.{userId}', function ($user, $userId) {
    return (int)$user->id === (int)$userId; 
});
