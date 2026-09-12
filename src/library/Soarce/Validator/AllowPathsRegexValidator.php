<?php

namespace Soarce\Validator;

class AllowPathsRegexValidator implements PathValidator
{
    private array $regex;

    public function __construct(array $rules)
    {
        $this->regex = $rules;
    }

    public function isValid(string $path): bool
    {
        return [] === $this->regex || array_any($this->regex, fn($rule) => preg_match($rule, $path));
    }
}