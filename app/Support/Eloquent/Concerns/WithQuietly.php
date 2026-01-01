<?php

namespace App\Support\Eloquent\Concerns;

trait WithQuietly
{
    /**
     * @return bool
     */
    public function fillQuietly(array $attributes, array $options = [])
    {
        return static::withoutTimestamps(fn () => $this->fill($attributes)->saveQuietly($options));
    }

    /**
     * @return bool
     */
    public function forceFillQuietly(array $attributes, array $options = [])
    {
        return static::withoutTimestamps(fn () => $this->forceFill($attributes)->saveQuietly($options));
    }
}
