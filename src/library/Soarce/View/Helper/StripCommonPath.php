<?php

namespace Soarce\View\Helper;

/**
 * Class FileSize
 *
 * strips a configured common path from a full path to create relative paths.
 *
 * @package Soarce\View\Helper
 */
class StripCommonPath
{
    public const string REPLACEMENT = './';

    public static function filter(string $path, string $common): string
    {
        if ($common === '' || $common === '/') {
            return $path;
        }

        if (!str_ends_with($common, '/')) {
            $common .= '/';
        }

        $common = preg_quote($common, '#');

        return preg_replace("#^{$common}#", self::REPLACEMENT, $path);
    }
}
