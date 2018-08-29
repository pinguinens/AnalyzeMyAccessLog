<?php

// Функция форматирования пикового потребления памяти
function formatBytes($bytes, $precision = 2)
{
    $units = array("B", "KB", "MB", "GB", "TB");

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . " " . $units[$pow];
}