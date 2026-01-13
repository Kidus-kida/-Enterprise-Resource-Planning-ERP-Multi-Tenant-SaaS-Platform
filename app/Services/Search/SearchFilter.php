<?php

namespace App\Services\Search;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class SearchFilter
{
    protected $query;
    protected array $filters;

    public function __construct($query, array $filters)
    {
        $this->query = $query;
        $this->filters = $filters;
    }

    public function apply()
    {
        foreach ($this->filters as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if (method_exists($this, $method = 'filter' . ucfirst(\Str::camel($key)))) {
                $this->$method($value);
            } else {
                // Default handling or strict avoidance?
                // For now, ignore unknown filters to prevent errors
            }
        }

        return $this->query;
    }

    // Common filters can be defined here or extended

    protected function filterSearch($term)
    {
        // This should be overridden or implemented based on model searchable fields
        // But providing a generic fallback if needed
    }
}
