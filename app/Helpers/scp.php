<?php
if (!function_exists('array_get')) {
    function array_get($array, $key, $default = null) {
        return Illuminate\Support\Arr::get($array, $key, $default);
    }
}