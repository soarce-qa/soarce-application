<?php

namespace Soarce\View\Helper;

use OutOfRangeException;

/**
 * Class FileSize
 *
 * View Helper to format bytes into human readable fashion
 *
 * @package Soarce\View\Helper
 */
class Bytes
{
    public const DEFINITIONS = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB', 'NiB', 'DiB'];

    /**
     * @param  int|float $bytes
     * @return string
     * @throws OutOfRangeException
     */
    public static function filter($bytes): string
    {
        if (!is_numeric($bytes)) {
            return '';
        }

        $bytes = (int)$bytes;

        $depth = 0;
        while (abs($bytes) >= 1024) {
            ++$depth;
            $bytes /= 1024;
        }

        if (count(self::DEFINITIONS) < $depth + 1) {
            throw new OutOfRangeException('We don\'t know more than DiB');
        }

        if (0 === $bytes) {
            $outbytes = '0';
        } else {
            $outbytes = number_format($bytes, $depth, ',', '');
        }

        if ($outbytes !== '0') {
            $outbytes = preg_replace('/,?0+$/', '', $outbytes);
        }

        $outbytes .= ' ' . self::DEFINITIONS[$depth];

        return $outbytes;
    }
}
