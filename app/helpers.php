<?php
use Carbon\Carbon;

if (! function_exists('time12')) {

    function time12($time, string $placeholder='-') : string
    {
        if ($time === null || $time === '') {
            return $placeholder;
        }
        try {
            if ($time instanceof Carbon) {
                return $time->format('g:i A');
            }
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
                return Carbon::createFromFormat('H:i:s', $time)->format('g:i A');
            }
            if (preg_match('/^\d{2}:\d{2}$/', $time)) {
                return Carbon::createFromFormat('H:i', $time)->format('g:i A');
            }
            return Carbon::parse($time)->format('g:i A');
        } catch (\Throwable $e) {
            return $placeholder;
        }
    }
}

if (! function_exists('dateTime12')) {

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

if (! function_exists('active_clinic')) {

    function active_clinic(): ?\App\Models\Clinic
    {
        $id = session('active_clinic_id');
        if (!$id) return null;
        static $cache = [];
        if (!array_key_exists($id, $cache)) {
            $cache[$id] = \App\Models\Clinic::find($id);
        }
        return $cache[$id];
    }
}
