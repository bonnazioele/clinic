<?php
use Carbon\Carbon;

if (! function_exists('time12')) {
    /**
     * Convert a time (string|Carbon|null) to 12-hour format like 1:05 PM.
     * Accepts:
     *  - 'HH:MM' or 'HH:MM:SS'
     *  - Carbon instance
     *  - null (returns '-')
     */
    function time12($time, string $placeholder='-') : string
    {
        if ($time === null || $time === '') {
            return $placeholder;
        }
        try {
            if ($time instanceof Carbon) {
                return $time->format('g:i A');
            }
            // If already includes seconds
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
                return Carbon::createFromFormat('H:i:s', $time)->format('g:i A');
            }
            // HH:MM only
            if (preg_match('/^\d{2}:\d{2}$/', $time)) {
                return Carbon::createFromFormat('H:i', $time)->format('g:i A');
            }
            // Try generic parse (may include date)
            return Carbon::parse($time)->format('g:i A');
        } catch (\Throwable $e) {
            return $placeholder;
        }
    }
}

if (! function_exists('dateTime12')) {
    /**
     * Format a Carbon|string date/time to 'M d, Y g:i A'.
     */
    function dateTime12($value, string $placeholder='-') : string
    {
        if ($value === null || $value === '') return $placeholder;
        try {
            $c = $value instanceof Carbon ? $value : Carbon::parse($value);
            return $c->format('M d, Y g:i A');
        } catch (\Throwable $e) {
            return $placeholder;
        }
    }
}
