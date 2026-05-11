<?php

// Test bootstrap file
require_once __DIR__ . '/../vendor/autoload.php';

// Mock WordPress functions for testing
if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        global $mockOptions;
        return $mockOptions[$option] ?? $default;
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return trim(strip_tags($str));
    }
}

if (!function_exists('wp_unslash')) {
    function wp_unslash($value) {
        return stripslashes_deep($value);
    }
}

if (!function_exists('size_format')) {
    function size_format($bytes) {
        return $bytes . ' bytes';
    }
}

if (!function_exists('stripslashes_deep')) {
    function stripslashes_deep($value) {
        return is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    }
}