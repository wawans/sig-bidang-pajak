<?php

namespace App\Support\Str;

trait Str
{
    public function strtobool($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    public function booltostr($value)
    {
        return (bool) $value ? 'true' : 'false';
    }
}
