<?php

namespace App\ModelFilters\Concerns;

trait Searchable
{
    /**
     * @param  null  $operator
     * @param  null  $value
     */
    protected function whereMatchOrLike($key, $operator = null, $value = null, string $boolean = 'and')
    {
        $operator = $operator ?: $this->input('search_operator', $this->input('operator', '%'));

        if ($operator == '%') {
            return $this->whereLike($key, $value, $boolean);
        }

        return $this->where($key, $operator, $value, $boolean);
    }

    /**
     * Search through searchable fields.
     *
     * @param  string  $value
     * @return $this
     */
    public function search($value)
    {
        return $this->where(function ($query) use ($value) {
            foreach ($this->getSearchable() as $key) {
                $query->whereLike($key, $value, 'or');
            }
        });
    }

    /**
     * Get searchable fields array
     */
    protected function getSearchable(): array
    {
        return [
            //
        ];
    }
}
