<?php

namespace Soarce\Validator;

class RejectPathsValidator extends RejectPathsRegexValidator
{
    public function __construct(array $rules)
    {
        if ([] === $rules) {
            parent::__construct([]);
            return;
        }

        $largeRegex = "/^(";
        foreach ($rules as &$rule) {
            $rule = preg_quote($rule, '/');
        }
        unset ($rule);
        $largeRegex .= implode('|', $rules) . ').*/';

        parent::__construct([$largeRegex]);
    }
}
