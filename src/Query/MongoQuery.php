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
    protected bool $analyzed = false;
    protected bool $cacheEnabled = false;
    protected array $highlightFields = [];
    protected ?int $timeoutMs = null;

    // Small clause cache to mirror ElasticQuery pattern (helps reuse identical stage fragments)
    protected static array $clauseCache = [];
    protected static int $cacheSize = 0;
    protected static int $maxCacheSize = 100;

    protected static function cacheClause(string $key, array $clause): void {
        if (self::$cacheSize >= self::$maxCacheSize) {
            $keysToRemove = array_slice(array_keys(self::$clauseCache), 0, 20);
            foreach ($keysToRemove as $keyToRemove) {
                unset(self::$clauseCache[$keyToRemove]);
                self::$cacheSize--;
            }
        }
        self::$clauseCache[$key] = $clause;
        self::$cacheSize++;
    }

    // Mongo-style clause helpers (static) - modeled after ElasticQuery helpers but returning Mongo structures
    public static function termClause(string $field, mixed $value): array {
        $cacheKey = "term:{$field}:" . (is_scalar($value) ? (string)$value : md5(serialize($value)));
        if (isset(self::$clauseCache[$cacheKey])) {
            return self::$clauseCache[$cacheKey];
        }
        $clause = [$field => $value];
        self::cacheClause($cacheKey, $clause);
        return $clause;
    }

    public static function rangeClause(string $field, string $operator, mixed $value): array {
        $map = [
            '>' => '$gt', 'gt' => '$gt',
            '>=' => '$gte', 'gte' => '$gte',
            '<' => '$lt', 'lt' => '$lt',
            '<=' => '$lte', 'lte' => '$lte'
        ];
        $op = $map[$operator] ?? ($map[strtolower($operator)] ?? '$eq');
        return [$field => [$op => $value]];
    }

    public static function existsClause(string $field): array {
        return [$field => ['$exists' => true]];
    }

    public static function termsClause(string $field, array $values): array {
        return [$field => ['$in' => $values]];
    }

    public static function notTermsClause(string $field, array $values): array {
        return ['$or' => [[$field => ['$nin' => $values]]]];
    }

    public static function regexClause(string $field, string $pattern, string $options = ''): array {
        $clause = [$field => ['$regex' => $pattern]];
        if ($options !== '') {
            $clause[$field]['$options'] = $options;
        }
        return $clause;
    }

    public static function matchStage(array $conditions): array {
        return ['$match' => $conditions];
    }

    // Additional helpers to further mirror ElasticQuery-style clause API
    public static function matchClause(string $field, mixed $value): array {
        return [$field => $value];
    }

    public static function textClause(string $term, array $options = []): array {
        return ['$text' => array_merge(['$search' => $term], $options)];
    }

    // Additional advanced helpers (Mongo equivalents for Elastic-like helpers)
    public static function prefixClause(string $field, string $prefix): array {
        // matches values starting with the given prefix
        $pattern = '^' . preg_quote($prefix, '/') . '.*';
        return [$field => ['$regex' => $pattern]];
    }

    public static function wildcardClause(string $field, string $pattern): array {
        // Support simple wildcard patterns where '*' => '.*' and '?' => '.'
        // Escape regex-special chars except '*' and '?'
        $escaped = preg_quote($pattern, '/');
        $escaped = str_replace(['\*', '\?'], ['*', '?'], $escaped);
        $regex = '^' . str_replace(['*', '?'], ['.*', '.'], $escaped) . '$';
        return [$field => ['$regex' => $regex]];
    }

    public static function termBoost(string $field, mixed $value, float $boost = 1.0): array {
        // MongoDB does not support per-clause boosting natively; keep structure compatible
        // by returning the same clause as termClause so higher-level code can interpret boost.
        return self::termClause($field, $value);
    }

    public static function matchBoost(array $conditions, float $boost = 1.0): array {
        // Wrapper that returns a $match stage; boost is informational and ignored by Mongo engine here
        return self::matchStage($conditions);
    }

    /**
     * Implementation of the get method from BaseQuery
     */
    public function get(): array
    {
        // Decide whether to return a find-style query or an aggregation pipeline
        // build() will return the appropriate representation depending on whether
        // aggregation stages were added. This preserves backwards compatibility
        // with tests that expect the find-format for simple queries.
        // Use the standardized internal buildQuery() adapter so both backends
        // expose the same protected method name (ElasticQuery already has
        // buildQuery()). buildQuery() delegates to the existing public build()
        // to preserve current behaviour.
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
        // In a real implementation, this would use MongoDB countDocuments
        return 0;
    }

    /**
     * Implementation of the paginate method from BaseQuery
     */
    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $total = $this->count();
        $currentPage = $page;
        
        $this->take($perPage);
        $this->offset(($currentPage - 1) * $perPage);
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
        // Use build() to return either the find-format (filter/options)
        // or the aggregation pipeline depending on the query contents.
        // Prefer the standardized internal buildQuery() adapter for parity
        // with ElasticQuery.
        return $this->buildQuery();
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
        $fieldName = str_starts_with($field, '$') ? $field : '$' . $field;
        
        $this->aggregationPipeline[] = [
            '$unwind' => $fieldName
        ];

        $this->logOperation('unwind', ['field' => $field]);

        return $this;
    }

    /**
     * Add a group operation
     */
    public function group(mixed $groupBy, array $operations = []): static
    {
        $this->useAggregation = true;
        
        if (is_string($groupBy)) {
            $groupStage = [
                '_id' => '$' . $groupBy
            ];
        } elseif (is_array($groupBy)) {
            if (isset($groupBy['_id'])) {
                $groupStage = $groupBy;
            } else {
                $groupStage = [
                    '_id' => $groupBy
                ];
            }
        } else {
            $groupStage = [
                '_id' => $groupBy
            ];
        }
        
        // Add operations
        if (!empty($operations)) {
            $groupStage = array_merge($groupStage, $operations);
        }
        
        $this->aggregationPipeline[] = [
            '$group' => $groupStage
        ];

        $this->logOperation('group', ['groupBy' => $groupBy, 'operations' => $operations]);

        return $this;
    }

    /**
     * Add a project operation (select specific fields)
     */
    public function project(array $fields): static
    {
        $this->useAggregation = true;
        $projection = [];
        
        foreach ($fields as $key => $value) {
            if (is_string($key)) {
                // Associative array: field name => expression
                $projection[$key] = $value;
            } elseif (is_string($value)) {
                // Numeric array: simple field selection
                $projection[$value] = 1;
            } elseif (is_array($value)) {
                // Merge complex expressions
                $projection = array_merge($projection, $value);
            }
        }

        $this->aggregationPipeline[] = [
            '$project' => $projection
        ];

        $this->logOperation('project', ['fields' => $fields]);

        return $this;
    }

    /**
     * Define os campos a serem retornados (projeção) - alias para project
     */
    public function source(array $fields): static
    {
        return $this->project($fields);
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
        $this->aggregationPipeline[] = self::matchStage($conditions);

        $this->logOperation('match', ['conditions' => $conditions]);

        return $this;
    }

    /**
     * Add a facet operation for multiple aggregations
     */
    public function facet(array $facets): static
    {
        $this->useAggregation = true;
        $this->aggregationPipeline[] = [
            '$facet' => $facets
        ];

        $this->logOperation('facet', ['facets' => $facets]);

        return $this;
    }

    /**
     * Override orderBy to not automatically enable aggregation
     */
    public function orderBy(string $field, string $direction = 'asc'): static
    {
        parent::orderBy($field, $direction);
        
        // Do NOT add $sort stage here to avoid duplications when buildAggregationPipeline
        // Sorting will be translated into aggregation stages at build time from $this->sorts

        return $this;
    }

    /**
     * Add a sort operation to aggregation pipeline (aggregation-specific)
     */
    public function sortAggregation(array $sort): static
    {
        $this->useAggregation = true;
        $this->aggregationPipeline[] = [
            '$sort' => $sort
        ];

        $this->logOperation('sortAggregation', ['sort' => $sort]);

        return $this;
    }

    /**
     * Override take to not automatically enable aggregation
     */
    public function take(int $limit): static
    {
        parent::take($limit);
        
        // Do NOT add $limit stage here; buildAggregationPipeline will translate limitValue into $limit

        return $this;
    }

    /**
     * Override skip to not automatically enable aggregation
     */
    public function skip(int $offset): static
    {
        parent::skip($offset);
        
        // Do NOT add $skip stage here; buildAggregationPipeline will translate offsetValue into $skip

        return $this;
    }

    /**
     * Add a limit operation to aggregation pipeline (aggregation-specific)
     */
    public function limitAggregation(int $limit): static
    {
        $this->useAggregation = true;
        $this->aggregationPipeline[] = [
            '$limit' => $limit
        ];

        $this->logOperation('limitAggregation', ['limit' => $limit]);

        return $this;
    }

    /**
     * Add a skip operation to aggregation pipeline (aggregation-specific)
     */
    public function skipAggregation(int $skip): static
    {
        $this->useAggregation = true;
        $this->aggregationPipeline[] = [
            '$skip' => $skip
        ];

        $this->logOperation('skipAggregation', ['skip' => $skip]);

        return $this;
    }

    /**
     * Add a sample operation
     */
    public function sample(int $size): static
    {
        $this->useAggregation = true;
        $this->aggregationPipeline[] = [
            '$sample' => ['size' => $size]
        ];

        $this->logOperation('sample', ['size' => $size]);

        return $this;
    }

    /**
     * Add addToSet operation
     */
    public function addToSet(string $field, string $expression): static
    {
        $this->useAggregation = true;
        $this->aggregationPipeline[] = [
            '$addFields' => [
                $field => ['$addToSet' => $expression]
            ]
        ];

        $this->logOperation('addToSet', ['field' => $field, 'expression' => $expression]);

        return $this;
    }

    /**
     * Add a geoNear aggregation stage
     */
    public function geoNear(array $options): static
    {
        $this->useAggregation = true;
        $this->aggregationPipeline[] = [
            '$geoNear' => $options
        ];

        $this->logOperation('geoNear', ['options' => $options]);

        return $this;
    }

    /**
     * Add fields to the aggregation pipeline
     */
    public function addFields(array $fields): static
    {
        $this->useAggregation = true;
        $this->aggregationPipeline[] = [
            '$addFields' => $fields
        ];

        $this->logOperation('addFields', ['fields' => $fields]);

        return $this;
    }

    /**
     * Adiciona uma etapa de agregação ao pipeline do MongoDB
     * Exemplo: aggregation('total', ['sum' => ['field' => 'price']])
     */
    public function aggregation(string $name, array $definition): static
    {
        $this->useAggregation = true;
        $stage = [];
        // Suporte básico para sum, terms, etc.
        if (isset($definition['sum'])) {
            $stage = ['$group' => ['_id' => null, $name => ['$sum' => '$' . $definition['sum']['field']]]];
        } elseif (isset($definition['terms'])) {
            $stage = ['$group' => [
                '_id' => '$' . $definition['terms']['field'],
                'count' => ['$sum' => 1],
            ]];
            if (isset($definition['terms']['size'])) {
                $stage = [
                    '$group' => [
                        '_id' => '$' . $definition['terms']['field'],
                        'count' => ['$sum' => 1],
                    ]
                ];
                $this->aggregationPipeline[] = $stage;
                $stage = ['$limit' => $definition['terms']['size']];
            }
        } else {
            // fallback: adiciona como está
            $stage = $definition;
        }
        $this->aggregationPipeline[] = $stage;
        return $this;
    }

    /**
     * Adiciona uma etapa de busca geográfica ($geoWithin/$near) ao pipeline do MongoDB
     * Exemplo: geoDistance('location', 'lat,long', '5km')
     */
    public function geoDistance(string $field, string $latlon, string $distance): static
    {
        $this->useAggregation = true;
        // Converte latlon para array [lat, lon]
        $coords = explode(',', $latlon);
        $lat = (float) trim($coords[0]);
        $lon = (float) trim($coords[1]);
        // Converte '5km' para metros
        $distMeters = (float) $distance * (stripos($distance, 'km') !== false ? 1000 : 1);
        $this->aggregationPipeline[] = [
            '$geoNear' => [
                'near' => ['type' => 'Point', 'coordinates' => [$lon, $lat]],
                'distanceField' => 'dist.calculated',
                'maxDistance' => $distMeters,
                'spherical' => true,
                'key' => $field
            ]
        ];
        return $this;
    }

    /**
     * Adiciona busca textual (full-text search) ao pipeline do MongoDB
     * Exemplo: search('termo', ['campo1', 'campo2'])
     */
    public function search(string $term, array $fields = []): static
    {
        $this->useAggregation = true;
        // MongoDB full-text search usa $text
        $this->aggregationPipeline[] = self::matchStage(self::textClause($term, []));
        return $this;
    }

    /**
     * Adiciona filtro por expressão regular ao pipeline do MongoDB
     * Exemplo: regexp('campo', 'regex')
     */
    public function regexp(string $field, string $pattern): static
    {
        $this->useAggregation = true;
        $this->aggregationPipeline[] = self::matchStage(self::regexClause($field, $pattern));
        return $this;
    }

    /**
     * Gera a query final (pipeline ou find) para MongoDB
     * Compatível com a interface do ElasticQuery (build())
     */
    public function build(): array
    {
        // Decide se é pipeline de agregação ou find simples
        if ($this->useAggregation || !empty($this->aggregationPipeline)) {
            return $this->buildAggregationPipeline();
        }
        return $this->buildFindQuery();
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
        // Only add a $sort stage if the pipeline does not already contain one
        if (!empty($this->sorts)) {
            $hasSort = false;
            foreach ($pipeline as $stage) {
                if (is_array($stage) && array_key_first($stage) === '$sort') {
                    $hasSort = true;
                    break;
                }
            }
            if (!$hasSort) {
                $pipeline[] = ['$sort' => $this->buildMongoSort()];
            }
        }

        // Add skip stage
        // Only add $skip if not already present in custom pipeline
        if ($this->offsetValue !== null) {
            $hasSkip = false;
            foreach ($pipeline as $stage) {
                if (is_array($stage) && array_key_first($stage) === '$skip') {
                    $hasSkip = true;
                    break;
                }
            }
            if (!$hasSkip) {
                $pipeline[] = ['$skip' => $this->offsetValue];
            }
        }

        // Add limit stage
        // Only add $limit if not already present in custom pipeline
        if ($this->limitValue !== null) {
            $hasLimit = false;
            foreach ($pipeline as $stage) {
                if (is_array($stage) && array_key_first($stage) === '$limit') {
                    $hasLimit = true;
                    break;
                }
            }
            if (!$hasLimit) {
                $pipeline[] = ['$limit' => $this->limitValue];
            }
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
                // Merge condition into filter, preserving multiple operators for same field
                foreach ($condition as $key => $val) {
                    // If key is a field name
                    if (str_starts_with($key, '$')) {
                        // Top-level operator (e.g. $or). Merge or set.
                        if (!isset($filter[$key])) {
                            $filter[$key] = $val;
                        } else {
                            // merge arrays
                            if (is_array($filter[$key]) && is_array($val)) {
                                $filter[$key] = array_merge($filter[$key], $val);
                            } else {
                                $filter[$key] = $val;
                            }
                        }
                        continue;
                    }

                    // Field-level merging
                    if (!isset($filter[$key])) {
                        $filter[$key] = $val;
                        continue;
                    }

                    // If existing is scalar (equality), convert to operator form
                    if (!is_array($filter[$key]) && is_array($val)) {
                        $filter[$key] = array_merge(['$eq' => $filter[$key]], $val);
                        continue;
                    }

                    if (is_array($filter[$key]) && is_array($val)) {
                        $filter[$key] = array_merge($filter[$key], $val);
                        continue;
                    }

                    // Fallback: overwrite
                    $filter[$key] = $val;
                }
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
            
            // Advanced MongoDB operators
            'regex' => [$field => ['$regex' => $value]],
            'size' => [$field => ['$size' => $value]],
            'exists' => [$field => ['$exists' => $value]],
            'type' => [$field => ['$type' => $value]],
            'all' => [$field => ['$all' => $value]],
            'elemMatch' => [$field => ['$elemMatch' => $value]],
            'mod' => [$field => ['$mod' => $value]],
            'near' => [$field => ['$near' => $value]],
            'geoWithin' => [$field => ['$geoWithin' => $value]],
            'geoIntersects' => [$field => ['$geoIntersects' => $value]],
            
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
        // Build a valid MongoDB $near query. If geometry is GeoJSON (has type/coordinates),
        // use $geometry; otherwise assume legacy coordinate array.
        $nearQuery = [];
        if (isset($geometry['type']) && isset($geometry['coordinates'])) {
            $nearQuery['$geometry'] = $geometry;
        } else {
            // legacy coordinates
            $nearQuery = $geometry;
        }

        if (isset($options['maxDistance'])) {
            $nearQuery['$maxDistance'] = $options['maxDistance'];
        }

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
        // Accept either GeoJSON geometry ({ type, coordinates }) or legacy envelope/shape
        $geo = [];
        if (isset($geometry['type']) && isset($geometry['coordinates'])) {
            $geo = ['$geometry' => $geometry];
        } else {
            // For legacy shapes, embed directly under $geometry if not already wrapped
            $geo = ['$geometry' => $geometry];
        }

        $this->match([
            $field => [
                '$geoWithin' => $geo
            ]
        ]);

        $this->logOperation('within', [
            'field' => $field,
            'geometry' => $geometry
        ]);

        return $this;
    }

    /**
     * Marca campos para destaque (highlight) - simulado para MongoDB
     */
    public function highlight(string|array $fields): static
    {
        // MongoDB não tem highlight nativo, mas podemos marcar para pós-processamento
        $this->highlightFields = (array) $fields;
        return $this;
    }

    /**
     * Define timeout para a query (simulado)
     */
    public function timeout(int $ms): static
    {
        $this->timeoutMs = $ms;
        return $this;
    }

    /**
     * Executa callback se condição for verdadeira
     */
    public function when($condition, callable $callback): static
    {
        if ($condition) {
            $callback($this);
        }
        return $this;
    }

    /**
     * Executa callback se condição for falsa
     */
    public function unless($condition, callable $callback): static
    {
        if (!$condition) {
            $callback($this);
        }
        return $this;
    }

    /**
     * Simula análise de query (para DX)
     */
    public function analyze(): static
    {
        $this->analyzed = true;
        return $this;
    }

    /**
     * Simula ativação de cache para a query
     */
    public function cache(bool $enable = true): static
    {
        $this->cacheEnabled = $enable;
        return $this;
    }

    /**
     * Escopo predefinido: apenas documentos ativos
     */
    public function active(): static
    {
        return $this->where('status', 'active');
    }

    /**
     * Escopo predefinido: apenas documentos publicados
     */
    public function published(): static
    {
        return $this->where('published', true);
    }

    /**
     * Escopo predefinido: documentos recentes (exemplo: últimos 30 dias)
     */
    public function recent(): static
    {
        $date = date('Y-m-d', strtotime('-30 days'));
        return $this->where('created_at', '>=', $date);
    }

    /**
     * Standardized protected buildQuery adapter (compatibility)
     *
     * ElasticQuery exposes a protected buildQuery(); provide the same
     * adapter on MongoQuery so both backends follow the same internal API.
     * This method delegates to the existing public build() to keep behaviour
     * identical to the current implementation.
     */
    protected function buildQuery(): array
    {
        return $this->build();
    }
}
