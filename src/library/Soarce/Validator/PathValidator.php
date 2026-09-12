<?php

namespace Soarce\Validator;
interface PathValidator
{
    public function isValid(string $path): bool;
}