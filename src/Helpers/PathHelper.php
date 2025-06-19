<?php

declare(strict_types=1);

if (!function_exists('globRecursive')) {
    function globRecursive(string $pattern, Closure $callback): void
    {
        foreach (glob($pattern, GLOB_BRACE) ?: [] as $file) {
            if (is_file($file)) {
                $callback($file);
            }
        }
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) ?: [] as $dir) {
            globRecursive($dir . '/' . basename($pattern), $callback);
        }
    }
}
