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

if (! function_exists('mask_string')) {

    function mask_string(?string $value, int $visibleStart = 1, int $visibleEnd = 1): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $length = mb_strlen($value);
        $visibleStart = max(0, min($visibleStart, $length));
        $visibleEnd = max(0, min($visibleEnd, $length - $visibleStart));
        if ($length <= $visibleStart + $visibleEnd) {
            return str_repeat('*', $length);
        }
        $maskedLength = $length - ($visibleStart + $visibleEnd);
        return mb_substr($value, 0, $visibleStart)
            . str_repeat('*', max($maskedLength, 1))
            . mb_substr($value, $length - $visibleEnd, $visibleEnd);
    }
}

if (! function_exists('mask_email')) {

    function mask_email(?string $email): string
    {
        if (!$email || !str_contains($email, '@')) {
            return $email ?? '';
        }
        [$local, $domain] = explode('@', $email, 2);
        $domainParts = explode('.', $domain);
        $primaryDomain = array_shift($domainParts) ?? '';
        $tld = implode('.', $domainParts);
        $maskedDomain = mask_string($primaryDomain, 1, 1);
        $rebuiltDomain = $maskedDomain . ($tld ? '.'.$tld : '');
        return mask_string($local, 1, 1) . '@' . $rebuiltDomain;
    }
}

if (! function_exists('mask_person')) {

    function mask_person(?string $name): string
    {
        if (!$name) {
            return '';
        }
        $parts = preg_split('/\s+/', trim($name));
        $maskedParts = array_map(function ($part) {
            return mask_string($part, 1, 1);
        }, array_filter($parts));
        return implode(' ', $maskedParts);
    }
}

if (! function_exists('safe_secretary_route')) {

    function safe_secretary_route(string $routeName, string $fallback = '/secretary/dashboard', array $params = []): string
    {
        if (!\Illuminate\Support\Facades\Route::has($routeName)) {
            return url($fallback);
        }

        try {
            return route($routeName, $params);
        } catch (\Throwable $firstError) {
            $activeClinicId = session('active_clinic_id');

            if ($activeClinicId) {
                try {
                    return route($routeName, array_merge(['clinic' => $activeClinicId], $params));
                } catch (\Throwable $secondError) {
                    return url($fallback);
                }
            }

            return url($fallback);
        }
    }
}
