<?php

use ebcore\Packages\Dump\Dump;

if (!function_exists('dd')) {
    /**
     * Dump the contents of a variable and then end execution of the script.
     *
     * @param mixed ...$vars
     * @return void
     */
    function dd(...$vars)
    {
        Dump::dd(...$vars);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump the contents of a variable.
     *
     * @param mixed ...$vars
     * @return void
     */
    function dump(...$vars)
    {
        Dump::dump(...$vars);
    }
}

// Register the functions globally
if (!function_exists('\\dd')) {
    function_exists('dd') or require __DIR__ . '/functions.php';
}

if (!function_exists('\\dump')) {
    function_exists('dump') or require __DIR__ . '/functions.php';
} 