<?php

namespace Arthur2weber\QueryCraft\Query;

/**
 * ElasticQuery - Elasticsearch Query Builder
 * 
 * Specialized query builder for Elasticsearch that extends the universal BaseQuery
 * with Elasticsearch-specific functionality while maintaining the familiar
 * Laravel Eloquent syntax.
 * 
 * @package Arthur2weber\QueryCraft\Query
 * @author Arthur Weber <arthur2weber@gmail.com>
 * @version 2.0.0
 */
class ElasticQuery extends BaseQuery
{
    protected array $query = [
        'query' => [
            'bool' => []
        ]
    ];

    protected array $validSortDirections = ['asc', 'desc'];
    protected static array $clauseCache = [];
    protected static int $cacheSize = 0;
    protected static int $maxCacheSize = 100;
    protected array $validRangeOperators = ['gte', 'lte', 'gt', 'lt'];
    protected array $validAggregationTypes = [
        'terms', 'date_histogram', 'histogram', 'range', 'stats', 'avg', 'sum', 'min', 'max',
        'value_count', 'cardinality', 'percentiles', 'percentile_ranks', 'extended_stats',
        'geo_distance', 'geo_bounds', 'geo_centroid', 'significant_terms', 'sampler',
        'nested', 'reverse_nested', 'children', 'parent', 'top_hits', 'scripted_metric',
        'bucket_script', 'bucket_selector', 'bucket_sort', 'cumulative_sum', 'derivative',
        'moving_avg', 'serial_diff', 'filters', 'global', 'missing', 'ip_range'
    ];

    protected ?string $index = null;
    protected array $aggregations = [];
    protected array $highlights = [];
    protected array $searchFields = [];
    protected ?string $searchTerm = null;

    /**
     * Set the Elasticsearch index to query
     */
    public function index(string $index): static
    {
        $this->index = $index;
        return $this->from($index);
    }

    /**
     * Implementation of the get method from BaseQuery
     */
    public function get(): array
    {
        return $this->buildQuery();
    }

    /**
     * Implementation of the first method from BaseQuery
     */
    public function first(): ?array
    {
        $originalLimit = $this->limitValue;
        $this->take(1);
        
        $results = $this->get();
        $this->limitValue = $originalLimit;
        
        return $results[0] ?? null;
    }

    /**
     * Implementation of the count method from BaseQuery
     */
    public function count(): int
    {
        // For now, return a mock count - this would integrate with actual Elasticsearch client
        return 0;
    }

    /**
     * Implementation of the paginate method from BaseQuery
     */
    public function paginate(int $perPage = 15): array
    {
        $total = $this->count();
        $currentPage = (int) (($this->offsetValue ?? 0) / $perPage) + 1;
        
        $this->take($perPage);
        $results = $this->get();

        return [
            'data' => $results,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => (int) ceil($total / $perPage),
            'from' => ($currentPage - 1) * $perPage + 1,
            'to' => min($currentPage * $perPage, $total)
        ];
    }

    /**
     * Implementation of the toQuery method from BaseQuery
     */
    public function toQuery(): array
    {
        return $this->buildElasticsearchQuery();
    }

    /**
     * Add a search query with multiple fields
     */
    public function search(string $term, array $fields = []): static
    {
        $this->searchTerm = $term;
        $this->searchFields = $fields;
        
        $this->logOperation('search', [
            'term' => $term,
            'fields' => $fields
        ]);

        return $this;
    }

    /**
     * Add highlighting for specific fields
     */
    public function highlight(array $fields): static
    {
        $this->highlights = array_merge($this->highlights, $fields);
        
        $this->logOperation('highlight', ['fields' => $fields]);

        return $this;
    }

    /**
     * Add an aggregation to the query
     */
    public function aggregation(string $name, array $agg): static
    {
        $this->validateAggregation($name, $agg);
        $this->aggregations[$name] = $agg;
        
        $this->logOperation('aggregation', [
            'name' => $name,
            'aggregation' => $agg
        ]);

        return $this;
    }

    // Elasticsearch-specific interface methods
    public static function nestedClause(string $path, array $query): array
    {
        return [
            'nested' => [
                'path' => $path,
                'query' => $query
            ]
        ];
    }

    public function minimumShouldMatch(string|int $value): self
    {
        $this->query['query']['bool']['minimum_should_match'] = $value;
        
        $this->logOperation('minimumShouldMatch', ['value' => $value]);
        
        return $this;
    }

    public function size(int $size): self
    {
        return $this->take($size);
    }

    public function must(array $condition): self
    {
        if (!isset($this->query['query']['bool']['must'])) {
            $this->query['query']['bool']['must'] = [];
        }
        $this->query['query']['bool']['must'][] = $condition;
        
        $this->logOperation('must', ['condition' => $condition]);
        
        return $this;
    }

    public function should(array $condition, string|int|null $minimumShouldMatch = null): self
    {
        if (!isset($this->query['query']['bool']['should'])) {
            $this->query['query']['bool']['should'] = [];
        }
        $this->query['query']['bool']['should'][] = $condition;
        
        if ($minimumShouldMatch !== null) {
            $this->query['query']['bool']['minimum_should_match'] = $minimumShouldMatch;
        }
        
        $this->logOperation('should', [
            'condition' => $condition,
            'minimum_should_match' => $minimumShouldMatch
        ]);
        
        return $this;
    }

    public function mustNot(array $condition): self
    {
        if (!isset($this->query['query']['bool']['must_not'])) {
            $this->query['query']['bool']['must_not'] = [];
        }
        $this->query['query']['bool']['must_not'][] = $condition;
        
        $this->logOperation('mustNot', ['condition' => $condition]);
        
        return $this;
    }

    /**
     * Override the where method to integrate with Elasticsearch bool query
     */
    public function where(string $field, mixed $operator, mixed $value = null): static
    {
        // Call parent method to maintain common functionality
        parent::where($field, $operator, $value);
        
        // Add to Elasticsearch bool query
        $condition = $this->buildElasticsearchCondition($field, $operator, $value);
        $this->must($condition);
        
        return $this;
    }

    /**
     * Build the complete Elasticsearch query
     */
    protected function buildElasticsearchQuery(): array
    {
        $query = $this->query;

        // Add search query if specified
        if ($this->searchTerm) {
            $searchQuery = $this->buildSearchQuery();
            $this->must($searchQuery);
        }

        // Add where conditions
        $this->addWhereConditions();

        // Add sorting
        if (!empty($this->sorts)) {
            $query['sort'] = $this->buildElasticsearchSort();
        }

        // Add pagination
        if ($this->limitValue !== null) {
            $query['size'] = $this->limitValue;
        }

        if ($this->offsetValue !== null) {
            $query['from'] = $this->offsetValue;
        }

        // Add aggregations
        if (!empty($this->aggregations)) {
            $query['aggs'] = $this->aggregations;
        }

        // Add highlighting
        if (!empty($this->highlights)) {
            $query['highlight'] = [
                'fields' => array_fill_keys($this->highlights, new \stdClass())
            ];
        }

        return $query;
    }

    /**
     * Build search query for full-text search
     */
    protected function buildSearchQuery(): array
    {
        if (empty($this->searchFields)) {
            return ['match' => ['_all' => $this->searchTerm]];
        }

        return [
            'multi_match' => [
                'query' => $this->searchTerm,
                'fields' => $this->searchFields
            ]
        ];
    }

    /**
     * Add where conditions to the bool query
     */
    protected function addWhereConditions(): void
    {
        foreach ($this->wheres as $where) {
            $condition = $this->buildElasticsearchCondition(
                $where['field'],
                $where['operator'],
                $where['value']
            );

            if ($where['boolean'] === 'or') {
                $this->should($condition);
            } else {
                $this->must($condition);
            }
        }
    }

    /**
     * Build an Elasticsearch condition from where clause
     */
    protected function buildElasticsearchCondition(string $field, string $operator, mixed $value): array
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        return match ($operator) {
            '=' => ['term' => [$field => $value]],
            '!=' => ['bool' => ['must_not' => ['term' => [$field => $value]]]],
            '>' => ['range' => [$field => ['gt' => $value]]],
            '>=' => ['range' => [$field => ['gte' => $value]]],
            '<' => ['range' => [$field => ['lt' => $value]]],
            '<=' => ['range' => [$field => ['lte' => $value]]],
            'in' => ['terms' => [$field => $value]],
            'not_in' => ['bool' => ['must_not' => ['terms' => [$field => $value]]]],
            'between' => ['range' => [$field => ['gte' => $value[0], 'lte' => $value[1]]]],
            'not_between' => ['bool' => ['must_not' => ['range' => [$field => ['gte' => $value[0], 'lte' => $value[1]]]]]],
            'null' => ['bool' => ['must_not' => ['exists' => ['field' => $field]]]],
            'not_null' => ['exists' => ['field' => $field]],
            'like' => ['wildcard' => [$field => str_replace('%', '*', $value)]],
            default => ['term' => [$field => $value]]
        };
    }

    /**
     * Build Elasticsearch sort array
     */
    protected function buildElasticsearchSort(): array
    {
        $sorts = [];
        foreach ($this->sorts as $sort) {
            $sorts[] = [$sort['field'] => ['order' => $sort['direction']]];
        }
        return $sorts;
    }

    /**
     * Validate aggregation configuration
     */
    protected function validateAggregation(string $name, array $agg): void
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Aggregation name cannot be empty');
        }

        if (empty($agg)) {
            throw new \InvalidArgumentException('Aggregation configuration cannot be empty');
        }
    }

    /**
     * Build the final query (compatibility method)
     */
    protected function buildQuery(): array
    {
        return $this->buildElasticsearchQuery();
    }

    // Additional Elasticsearch-specific methods can be added here...
    
    public function fuzzy(string $field, string $value, array $options = []): self
    {
        $condition = [
            'fuzzy' => [
                $field => array_merge(['value' => $value], $options)
            ]
        ];
        
        return $this->must($condition);
    }

    public function wildcard(string $field, string $pattern): self
    {
        $condition = ['wildcard' => [$field => $pattern]];
        return $this->must($condition);
    }

    public function exists(string $field): self
    {
        $condition = ['exists' => ['field' => $field]];
        return $this->must($condition);
    }

    public function missing(string $field): self
    {
        $condition = ['bool' => ['must_not' => ['exists' => ['field' => $field]]]];
        return $this->must($condition);
    }
}
