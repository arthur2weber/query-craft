<?php

namespace Arthur2weber\QueryCraft\Query;

/**
 * MongoQuery - MongoDB Query Builder
 * 
 * Specialized query builder for MongoDB that extends the universal BaseQuery
 * with MongoDB-specific functionality while maintaining the familiar
 * Laravel Eloquent syntax.
 * 
 * @package Arthur2weber\QueryCraft\Query
 * @author Arthur Weber <arthur2weber@gmail.com>
 * @version 1.0.0
 */
class MongoQuery extends BaseQuery
{
    protected array $pipeline = [];
    protected array $aggregationPipeline = [];
    protected bool $useAggregation = false;

    /**
     * Implementation of the get method from BaseQuery
     */
    public function get(): array
    {
        $pipeline = $this->buildMongoPipeline();
        
        // In a real implementation, this would use MongoDB client
        // For now, return the pipeline for demonstration
        return $pipeline;
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
        // In a real implementation, this would use MongoDB countDocuments
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
        return $this->buildMongoPipeline();
    }

    // MongoDB-specific methods

    /**
     * Add a lookup (join) operation
     */
    public function lookup(string $from, string $localField, string $foreignField, string $as): static
    {
        $this->useAggregation = true;
        $this->aggregationPipeline[] = [
            '$lookup' => [
                'from' => $from,
                'localField' => $localField,
                'foreignField' => $foreignField,
                'as' => $as
            ]
        ];

        $this->logOperation('lookup', [
            'from' => $from,
            'localField' => $localField,
            'foreignField' => $foreignField,
            'as' => $as
        ]);

        return $this;
    }

    /**
     * Add an unwind operation
     */
    public function unwind(string $field): static
    {
        $this->useAggregation = true;
        $this->aggregationPipeline[] = [
            '$unwind' => $field
        ];

        $this->logOperation('unwind', ['field' => $field]);

        return $this;
    }

    /**
     * Add a group operation
     */
    public function group(array $groupBy): static
    {
        $this->useAggregation = true;
        $this->aggregationPipeline[] = [
            '$group' => $groupBy
        ];

        $this->logOperation('group', ['groupBy' => $groupBy]);

        return $this;
    }

    /**
     * Add a project operation (select specific fields)
     */
    public function project(array $fields): static
    {
        $this->useAggregation = true;
        $projection = [];
        
        foreach ($fields as $field) {
            if (is_string($field)) {
                $projection[$field] = 1;
            } elseif (is_array($field)) {
                $projection = array_merge($projection, $field);
            }
        }

        $this->aggregationPipeline[] = [
            '$project' => $projection
        ];

        $this->logOperation('project', ['fields' => $fields]);

        return $this;
    }

    /**
     * Override select to work with MongoDB projection
     */
    public function select(array $fields): static
    {
        parent::select($fields);
        
        // If we're not using aggregation, we'll use find() projection
        if (!$this->useAggregation) {
            return $this;
        }

        return $this->project($fields);
    }

    /**
     * Add a match operation (equivalent to where)
     */
    public function match(array $conditions): static
    {
        $this->useAggregation = true;
        $this->aggregationPipeline[] = [
            '$match' => $conditions
        ];

        $this->logOperation('match', ['conditions' => $conditions]);

        return $this;
    }

    /**
     * Build the complete MongoDB pipeline
     */
    protected function buildMongoPipeline(): array
    {
        $pipeline = [];

        // If using aggregation pipeline
        if ($this->useAggregation) {
            return $this->buildAggregationPipeline();
        }

        // Build standard find() query
        return $this->buildFindQuery();
    }

    /**
     * Build aggregation pipeline
     */
    protected function buildAggregationPipeline(): array
    {
        $pipeline = [];

        // Add match stage for where conditions
        if (!empty($this->wheres)) {
            $pipeline[] = ['$match' => $this->buildMongoFilter()];
        }

        // Add custom aggregation stages
        $pipeline = array_merge($pipeline, $this->aggregationPipeline);

        // Add sort stage
        if (!empty($this->sorts)) {
            $pipeline[] = ['$sort' => $this->buildMongoSort()];
        }

        // Add skip stage
        if ($this->offsetValue !== null) {
            $pipeline[] = ['$skip' => $this->offsetValue];
        }

        // Add limit stage
        if ($this->limitValue !== null) {
            $pipeline[] = ['$limit' => $this->limitValue];
        }

        return $pipeline;
    }

    /**
     * Build simple find query
     */
    protected function buildFindQuery(): array
    {
        $query = [
            'filter' => $this->buildMongoFilter(),
            'options' => []
        ];

        // Add sorting
        if (!empty($this->sorts)) {
            $query['options']['sort'] = $this->buildMongoSort();
        }

        // Add pagination
        if ($this->limitValue !== null) {
            $query['options']['limit'] = $this->limitValue;
        }

        if ($this->offsetValue !== null) {
            $query['options']['skip'] = $this->offsetValue;
        }

        // Add projection
        if ($this->selects !== ['*']) {
            $projection = [];
            foreach ($this->selects as $field) {
                $projection[$field] = 1;
            }
            $query['options']['projection'] = $projection;
        }

        return $query;
    }

    /**
     * Build MongoDB filter from where conditions
     */
    protected function buildMongoFilter(): array
    {
        if (empty($this->wheres)) {
            return [];
        }

        $filter = [];
        $orConditions = [];

        foreach ($this->wheres as $where) {
            $condition = $this->buildMongoCondition(
                $where['field'],
                $where['operator'],
                $where['value']
            );

            if ($where['boolean'] === 'or') {
                $orConditions[] = $condition;
            } else {
                $filter = array_merge($filter, $condition);
            }
        }

        // Add OR conditions
        if (!empty($orConditions)) {
            if (!empty($filter)) {
                $filter = [
                    '$and' => [
                        $filter,
                        ['$or' => $orConditions]
                    ]
                ];
            } else {
                $filter['$or'] = $orConditions;
            }
        }

        return $filter;
    }

    /**
     * Build a MongoDB condition from where clause
     */
    protected function buildMongoCondition(string $field, string $operator, mixed $value): array
    {
        return match ($operator) {
            '=' => [$field => $value],
            '!=' => [$field => ['$ne' => $value]],
            '>' => [$field => ['$gt' => $value]],
            '>=' => [$field => ['$gte' => $value]],
            '<' => [$field => ['$lt' => $value]],
            '<=' => [$field => ['$lte' => $value]],
            'in' => [$field => ['$in' => $value]],
            'not_in' => [$field => ['$nin' => $value]],
            'between' => [$field => ['$gte' => $value[0], '$lte' => $value[1]]],
            'not_between' => [
                '$or' => [
                    [$field => ['$lt' => $value[0]]],
                    [$field => ['$gt' => $value[1]]]
                ]
            ],
            'null' => [$field => null],
            'not_null' => [$field => ['$ne' => null]],
            'like' => [$field => ['$regex' => str_replace('%', '.*', $value), '$options' => 'i']],
            default => [$field => $value]
        };
    }

    /**
     * Build MongoDB sort array
     */
    protected function buildMongoSort(): array
    {
        $sort = [];
        foreach ($this->sorts as $sortItem) {
            $sort[$sortItem['field']] = $sortItem['direction'] === 'desc' ? -1 : 1;
        }
        return $sort;
    }

    /**
     * Add a text search operation
     */
    public function textSearch(string $text, array $options = []): static
    {
        $textQuery = array_merge(
            ['$search' => $text],
            $options
        );

        $this->match(['$text' => $textQuery]);

        $this->logOperation('textSearch', [
            'text' => $text,
            'options' => $options
        ]);

        return $this;
    }

    /**
     * Add a geo near operation
     */
    public function near(string $field, array $geometry, array $options = []): static
    {
        $nearQuery = array_merge([
            'near' => $geometry
        ], $options);

        $this->match([$field => ['$near' => $nearQuery]]);

        $this->logOperation('near', [
            'field' => $field,
            'geometry' => $geometry,
            'options' => $options
        ]);

        return $this;
    }

    /**
     * Add a geo within operation
     */
    public function within(string $field, array $geometry): static
    {
        $this->match([
            $field => [
                '$geoWithin' => [
                    '$geometry' => $geometry
                ]
            ]
        ]);

        $this->logOperation('within', [
            'field' => $field,
            'geometry' => $geometry
        ]);

        return $this;
    }
}
