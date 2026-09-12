<?php

namespace Soarce\Validator;

class RejectPathsRegexValidator implements PathValidator
{
    private array $regex;

    public function __construct(array $rules)
    {
        $this->regex = $rules;
    }

    public function isValid(string $path): bool
    {
        if ([] === $this->regex) {
            return true;
        }

        return ! array_any($this->regex, fn($rule) => preg_match($rule, $path));
    }
}