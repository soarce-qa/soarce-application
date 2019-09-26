<?php

namespace Soarce\View\Helper;

use Soarce\Model\SequenceRequest;

/**
 * Class FileSize
 *
 * View Helper to format bytes into human readable fashion
 *
 * @package Soarce\View\Helper
 */
class SequenceDiagram
{
    private const CLIENT = 'Client';
    private const ARROW  = '->>';

    /**
     * @param  SequenceRequest $rootElement
     * @param  string[]        $applications
     * @return string
     */
    public static function filter(SequenceRequest $rootElement, $applications): string
    {
        $output = "sequenceDiagram\n    participant " . self::CLIENT . "\n";
        foreach ($applications as $application) {
            $output .= "    participant {$application}\n";
        }

        $output .= self::renderSequence($rootElement);

        return $output;
    }

    /**
     * @param SequenceRequest $request
     * @return string
     */
    private static function renderSequence(SequenceRequest $request): string
    {
        $out = '    ';
        if (null === $request->getParent()) {
            $out .= self::CLIENT . self::ARROW . $request->getApplicationName();
        } else {
            $out .= $request->getParent()->getApplicationName() . self::ARROW . $request->getApplicationName();
        }

        $out .= ': ' . $request->getRequestId() . "\n";

        foreach ($request->getChildren() as $child) {
            $out .= self::renderSequence($child);
        }

        $out .= '    ';
        if (null === $request->getParent()) {
            $out .= $request->getApplicationName() . self::ARROW . self::CLIENT . ": return\n";
        } else {
            $out .= $request->getApplicationName() . self::ARROW . $request->getParent()->getApplicationName() . ": return\n";
        }

        return $out;
    }
}
