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
    public const REPLACEMENT = './';

    /**
     * @param  string $path
     * @param  string $common
     * @return string
     */
    public static function filter($path, $common): string
    {
        if ($common === '' || $common === '/') {
            return $path;
        }

        if (substr($common, -1) !== '/') {
            $common .= '/';
        }

        $common = preg_quote($common, '#');

        return preg_replace("#^{$common}#", self::REPLACEMENT, $path);
    }
}
