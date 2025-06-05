<?php

namespace Soarce\Twig;

use Soarce\Model\SequenceRequest;
use Soarce\View\Helper\Bytes;
use Soarce\View\Helper\SequenceDiagram;
use Soarce\View\Helper\StripCommonPath;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigTest;

/**
 * Class TwigExtension
 */
class TwigExtension
{
    public static function registerCustomTests(Environment $twig): void
    {
        $twig->addTest(new TwigTest('instanceof', static function ($variable, $classname) { return $variable instanceof $classname; }));
    }

    public static function registerFilters(Environment $twig): void
    {
        $filter = new TwigFilter('byte', static function ($bytes) {
            return Bytes::filter($bytes);
        });
        $twig->addFilter($filter);

        $filter = new TwigFilter('bin2hex', static function ($input) {
            return bin2hex($input);
        });
        $twig->addFilter($filter);

        $filter = new TwigFilter('unique', static function (array $arr) {
            return array_unique($arr);
        });
        $twig->addFilter($filter);

        $filter = new TwigFilter('preg_replace', static function ($input, $pattern, $replacement, $limit = -1): string {
            return preg_replace($pattern, $replacement, $input, $limit);
        });
        $twig->addFilter($filter);

        $filter = new TwigFilter('stripCommonPath', static function ($path, $commonPath) {
            return StripCommonPath::filter($path, $commonPath);
        });
        $twig->addFilter($filter);

        $filter = new TwigFilter('sequenceDiagram', static function (SequenceRequest $rootElement, $applications) {
            return SequenceDiagram::filter($rootElement, $applications);
        }, ['is_safe' => ['html']]);
        $twig->addFilter($filter);
    }
}