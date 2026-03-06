<?php

if (!function_exists('formatKenyanPhone')) {
    function formatKenyanPhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove spaces, dashes, brackets, etc.
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Remove leading +
        $phone = ltrim($phone, '+');

        // If already starts with 254 and is correct length, return it
        if (preg_match('/^2547\d{8}$/', $phone)) {
            return $phone;
        }

        // If starts with 07xxxxxxxx
        if (preg_match('/^07\d{8}$/', $phone)) {
            return '254' . substr($phone, 1);
        }

        // If starts with 7xxxxxxxx (missing leading 0)
        if (preg_match('/^7\d{8}$/', $phone)) {
            return '254' . $phone;
        }

        // If starts with 01xxxxxxxx (new Safaricom prefixes)
        if (preg_match('/^01\d{8}$/', $phone)) {
            return '254' . substr($phone, 1);
        }

        // If already starts with 254 but slightly malformed, just return cleaned version
        if (str_starts_with($phone, '254')) {
            return $phone;
        }

        return $phone; // fallback
    }
}