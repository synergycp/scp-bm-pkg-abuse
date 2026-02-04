<?php
if (!function_exists('array_get')) {
    function array_get($array, $key, $default = null) {
        return Illuminate\Support\Arr::get($array, $key, $default);
    }
}
if (!function_exists('str_limit')) {
    function str_limit($value, $limit = 100, $end = '...', $preserveWords = false)
    {
        return Illuminate\Support\Str::limit(
            $value,
            $limit,
            $end,
            $preserveWords
        );
    }
}