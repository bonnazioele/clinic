<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/**
 * Private channel for secretary notifications
 * Only authenticated users who are secretaries for the clinic can listen
 */
Broadcast::channel('user.notifications.secretary', function ($user) {
    // Ensure the user is a secretary
    return $user->is_secretary === 1;
});

/**
 * Optional: Private channel per secretary by ID
 * Allows sending notifications to a specific secretary only
 */
Broadcast::channel('secretary.{id}', function ($user, $id) {
    return $user->is_secretary === 1 && $user->id == $id;
});

/**
 * Example: Doctor-specific private channel
 * If you ever want to notify doctors
 */
Broadcast::channel('user.notifications.doctor', function ($user) {
    return $user->is_doctor === 1;
});
