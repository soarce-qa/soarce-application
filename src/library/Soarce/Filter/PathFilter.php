<?php

namespace Soarce\Filter;

use Generator;
use Soarce\Config\Service;
use Soarce\Validator\AllowPathsRegexValidator;
use Soarce\Validator\AllowPathsValidator;
use Soarce\Validator\RejectPathsRegexValidator;
use Soarce\Validator\RejectPathsValidator;

class PathFilter {
    private AllowPathsValidator $allowPathsValidator;
    private AllowPathsRegexValidator $allowPathsRegexValidator;
    private RejectPathsValidator $rejectPathsValidator;
    private RejectPathsRegexValidator $rejectPathsRegexValidator;

    public function __construct(array $allowPaths = [], array $allowPathsRegex = [], array $rejectPaths = [], array $rejectPathsRegex = [])
    {
        $this->allowPathsValidator = new AllowPathsValidator($allowPaths);
        $this->allowPathsRegexValidator = new AllowPathsRegexValidator($allowPathsRegex);
        $this->rejectPathsValidator = new RejectPathsValidator($rejectPaths);
        $this->rejectPathsRegexValidator = new RejectPathsRegexValidator($rejectPathsRegex);
    }

    public static function fromServiceConfig(Service $service): self
    {
        $filters = $service->getFilters();
        return new self(
            $filters['allow'] ?? [],
            $filters['allow_regex'] ?? [],
            $filters['reject'] ?? [],
            $filters['reject_regex'] ?? [],
        );
    }

    public function filterArrayKeys(array $list): Generator
    {
        foreach ($list as $key => $value) {
            if (
                ($this->allowPathsValidator->isValid($key) || $this->allowPathsRegexValidator->isValid($key))
                && $this->rejectPathsValidator->isValid($key) && $this->rejectPathsRegexValidator->isValid($key)) {
                yield $key => $value;
            }
        }
    }
}
