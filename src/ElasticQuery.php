<?php

namespace Arthur2weber\QueryCraft;

/**
 * QueryCraft - Fluent DSL Query Builder
 * 
 * A powerful, intuitive query builder for multiple DSL engines that brings the familiar
 * Laravel Eloquent syntax to Elasticsearch, MongoDB, and GraphQL queries. Build complex search
 * queries with ease using method chaining and fluent interfaces.
 * 
 * @package Arthur2weber\QueryCraft
 * @author Arthur Weber <arthur2weber@gmail.com>
 * @version 1.0.0
 * 
 * @example Basic search with filters
 * $query = new ElasticQuery();
 * $results = $query
 *     ->search('Laravel PHP', ['title^2', 'content'])
 *     ->where('status', 'published')
 *     ->whereIn('category', ['programming', 'tutorial'])
 *     ->recent(30)
 *     ->orderByDesc('views')
 *     ->paginate(15);
 * 
 * @example Complex e-commerce query
 * $products = (new ElasticQuery())
 *     ->search('wireless headphones', ['name^3', 'description', 'brand^2'])
 *     ->whereBetween('price', [50, 300])
 *     ->where('rating', '>=', 4)
 *     ->where('in_stock', true)
 *     ->whereIn('brand', ['sony', 'bose', 'sennheiser'])
 *     ->aggregation('brands', ['terms' => ['field' => 'brand.keyword']])
 *     ->orderByDesc('_score')
 *     ->paginate(20);
 * 
 * @example Geographic search
 * $restaurants = (new ElasticQuery())
 *     ->search('italian restaurant')
 *     ->must(['geo_distance' => [
 *         'distance' => '5km',
 *         'location' => ['lat' => 40.7128, 'lon' => -74.0060]
 *     ]])
 *     ->where('rating', '>=', 4)
 *     ->orderBy('_geo_distance');
 */
class ElasticQuery implements ElasticQueryInterface {
    protected array $query = [
        'query' => [
            'bool' => []
        ]
    ];

    protected array $validSortDirections = ['asc', 'desc'];
    
    // Simple cache for static clause builders
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

    // Developer Experience Enhancement Properties
    protected bool $verboseMode = false;
    protected array $buildLog = [];
    protected array $warnings = [];
    protected array $performanceWarnings = [];

    /**
     * Validates field names to ensure they are valid for Elasticsearch
     * 
     * @param string $field Field name to validate
     * @throws \InvalidArgumentException If field name is invalid
     */
    protected function validateFieldName(string $field): void {
        // Quick empty check first (most common validation)
        if ($field === '' || ctype_space($field)) {
            throw new \InvalidArgumentException('Field name cannot be empty');
        }
        
        // Only check for spaces if we need to warn (less common)
        if (strpos($field, ' ') !== false) {
            trigger_error("Field name '$field' contains spaces. This might cause issues depending on Elasticsearch configuration.", E_USER_WARNING);
        }
    }

    /**
     * Validates values to ensure they can be safely JSON encoded
     * 
     * @param mixed $value Value to validate
     * @throws \InvalidArgumentException If value cannot be JSON encoded
     */
    protected function validateValue($value): void {
        // Check for unsupported types
        if (is_resource($value) || is_callable($value)) {
            throw new \InvalidArgumentException('Unsupported data type: ' . gettype($value) . '. Resources and callables cannot be JSON encoded.');
        }
        
        // Check for infinite or NaN numbers
        if (is_numeric($value) && !is_finite($value)) {
            throw new \InvalidArgumentException('Infinite and NaN values are not supported in Elasticsearch queries');
        }
        
        // Check for valid UTF-8 encoding if it's a string
        if (is_string($value) && function_exists('mb_check_encoding') && !\mb_check_encoding($value, 'UTF-8')) {
            throw new \InvalidArgumentException('Invalid UTF-8 encoding detected in string value');
        }
        
        // Check for control characters that might cause JSON encoding issues
        if (is_string($value) && preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
            throw new \InvalidArgumentException('String contains control characters that may cause JSON encoding issues');
        }
        
        // Check for extremely long strings (> 32KB)
        if (is_string($value) && strlen($value) > 32768) {
            trigger_error('String value is very long (' . strlen($value) . ' bytes). This may cause performance issues.', E_USER_WARNING);
        }
    }

    /**
     * Validates arrays to ensure they are not empty when required
     * 
     * @param array $array Array to validate
     * @param string $context Context for error message
     * @throws \InvalidArgumentException If array is empty
     */
    protected function validateNonEmptyArray(array $array, string $context = 'Array'): void {
        if (empty($array)) {
            throw new \InvalidArgumentException("$context cannot be empty");
        }
    }

    /**
     * Validates aggregation configuration
     * 
     * @param string $name Aggregation name
     * @param array $agg Aggregation configuration
     * @throws \InvalidArgumentException If aggregation is invalid
     */
    protected function validateAggregation(string $name, array $agg): void {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Aggregation name cannot be empty');
        }
        
        if (empty($agg)) {
            throw new \InvalidArgumentException('Aggregation configuration cannot be empty');
        }
        
        // Check if aggregation type is valid
        $aggType = array_key_first($agg);
        if ($aggType && !in_array($aggType, $this->validAggregationTypes, true)) {
            trigger_error("Unknown aggregation type '$aggType'. This may cause errors in Elasticsearch.", E_USER_WARNING);
        }
    }

    /**
     * Validates geo coordinates
     * 
     * @param mixed $lat Latitude
     * @param mixed $lon Longitude
     * @throws \InvalidArgumentException If coordinates are invalid
     */
    protected function validateGeoCoordinates($lat, $lon): void {
        if (!is_numeric($lat) || !is_numeric($lon)) {
            throw new \InvalidArgumentException('Geo coordinates must be numeric values');
        }
        
        $lat = (float) $lat;
        $lon = (float) $lon;
        
        if ($lat < -90 || $lat > 90) {
            throw new \InvalidArgumentException('Latitude must be between -90 and 90 degrees');
        }
        
        if ($lon < -180 || $lon > 180) {
            throw new \InvalidArgumentException('Longitude must be between -180 and 180 degrees');
        }
    }

    /**
     * Validates script content
     * 
     * @param string $script Script content
     * @throws \InvalidArgumentException If script is invalid
     */
    protected function validateScript(string $script): void {
        if (empty(trim($script))) {
            throw new \InvalidArgumentException('Script cannot be empty');
        }
    }

    /**
     * Recursively validates values in condition arrays
     * 
     * @param array $condition Condition array to validate
     * @param int $depth Current recursion depth
     * @param int $maxDepth Maximum allowed recursion depth
     */
    protected function validateConditionValues(array $condition, int $depth = 0, int $maxDepth = 50): void {
        // Prevent infinite recursion
        if ($depth > $maxDepth) {
            throw new \InvalidArgumentException('Maximum recursion depth exceeded in condition validation');
        }
        
        foreach ($condition as $key => $value) {
            if (is_array($value)) {
                $this->validateConditionValues($value, $depth + 1, $maxDepth);
            } else {
                $this->validateValue($value);
            }
            
            // Validate field names if they look like field names
            if (is_string($key) && !in_array($key, ['query', 'boost', 'operator', 'minimum_should_match', 'value', 'gte', 'lte', 'gt', 'lt', 'from', 'to', 'distance', 'lat', 'lon', 'order', 'format', 'time_zone', 'field', 'fields', 'size', 'interval', 'calendar_interval', 'fixed_interval', 'time_zone', 'offset', 'keyed', 'min_doc_count'])) {
                $this->validateFieldName($key);
            }
        }
    }

    /**
     * Checks for potential logical conflicts in queries
     * 
     * @param string $field Field name
     * @param string $operator Operator being used
     * @param mixed $value Value being compared
     */
    protected function checkForLogicalConflicts(string $field, string $operator, $value): void {
        // Check for must/must_not conflicts
        $mustClauses = $this->query['query']['bool']['must'] ?? [];
        $mustNotClauses = $this->query['query']['bool']['must_not'] ?? [];
        
        foreach ($mustClauses as $mustClause) {
            foreach ($mustNotClauses as $mustNotClause) {
                if ($this->areClausesConflicting($mustClause, $mustNotClause, $field, $value)) {
                    trigger_error("Potential logical conflict detected: field '$field' has both must and must_not conditions that may never match.", E_USER_WARNING);
                }
            }
        }
    }

    /**
     * Checks if two clauses are conflicting
     * 
     * @param array $clause1 First clause
     * @param array $clause2 Second clause
     * @param string $field Current field
     * @param mixed $value Current value
     * @return bool True if conflicting
     */
    protected function areClausesConflicting(array $clause1, array $clause2, string $field, $value): bool {
        // Simple check for exact match conflicts
        if (isset($clause1['match'][$field]) && isset($clause2['match'][$field])) {
            return $clause1['match'][$field] === $clause2['match'][$field];
        }
        
        if (isset($clause1['term'][$field]) && isset($clause2['term'][$field])) {
            return $clause1['term'][$field] === $clause2['term'][$field];
        }
        
        return false;
    }

    /**
     * Validates geo queries in conditions
     * 
     * @param array $condition Condition to validate
     */
    protected function validateGeoQueries(array $condition): void {
        if (isset($condition['geo_distance'])) {
            $geoDistance = $condition['geo_distance'];
            
            // Validate distance format
            if (isset($geoDistance['distance']) && is_string($geoDistance['distance'])) {
                if (!preg_match('/^\d+(\.\d+)?(km|m|mi|yd|ft|in|mm|cm|nmi)$/', $geoDistance['distance'])) {
                    throw new \InvalidArgumentException("Invalid geo_distance format: {$geoDistance['distance']}. Use format like '5km', '100m', etc.");
                }
            }
            
            // Validate coordinates
            foreach ($geoDistance as $field => $location) {
                if ($field !== 'distance' && is_array($location)) {
                    if (isset($location['lat'], $location['lon'])) {
                        $this->validateGeoCoordinates($location['lat'], $location['lon']);
                    }
                }
            }
        }
        
        if (isset($condition['geo_bounding_box'])) {
            $geoBBox = $condition['geo_bounding_box'];
            
            foreach ($geoBBox as $field => $bounds) {
                if (is_array($bounds)) {
                    if (isset($bounds['top_left'], $bounds['bottom_right'])) {
                        // Validate bounding box coordinates if they are arrays
                        if (is_array($bounds['top_left']) && isset($bounds['top_left']['lat'], $bounds['top_left']['lon'])) {
                            $this->validateGeoCoordinates($bounds['top_left']['lat'], $bounds['top_left']['lon']);
                        }
                        if (is_array($bounds['bottom_right']) && isset($bounds['bottom_right']['lat'], $bounds['bottom_right']['lon'])) {
                            $this->validateGeoCoordinates($bounds['bottom_right']['lat'], $bounds['bottom_right']['lon']);
                        }
                    }
                }
            }
        }
    }

    public function must(array $condition): self {
        // Validate the condition structure
        if (empty($condition)) {
            throw new \InvalidArgumentException('Must condition cannot be empty');
        }
        
        // Special validation for geo queries (simplified - no recursive validation for now)
        $this->validateGeoQueries($condition);
        
        $this->query['query']['bool']['must'][] = $condition;
        return $this;
    }

    public function should(array $condition, string|int|null $minimumShouldMatch = null): self {
        // Validate the condition structure
        if (empty($condition)) {
            throw new \InvalidArgumentException('Should condition cannot be empty');
        }
        
        $this->query['query']['bool']['should'][] = $condition;
        
        // Set minimum_should_match if provided
        if ($minimumShouldMatch !== null) {
            $this->query['query']['bool']['minimum_should_match'] = $minimumShouldMatch;
        }
        
        return $this;
    }

    public function mustNot(array $condition): self {
        // Validate the condition structure
        if (empty($condition)) {
            throw new \InvalidArgumentException('Must not condition cannot be empty');
        }
        
        $this->query['query']['bool']['must_not'][] = $condition;
        return $this;
    }

    public function minimumShouldMatch(string|int $value): self {
        // Validate that minimum_should_match doesn't exceed should clauses count
        $shouldCount = count($this->query['query']['bool']['should'] ?? []);
        if (is_numeric($value) && $value > $shouldCount && $shouldCount > 0) {
            throw new \InvalidArgumentException("minimum_should_match value ($value) cannot be greater than the number of should clauses ($shouldCount)");
        }
        
        // Additional validation for string values (percentages)
        if (is_string($value) && preg_match('/^(\d+)%$/', $value, $matches)) {
            $percentage = (int)$matches[1];
            if ($percentage > 100) {
                throw new \InvalidArgumentException("minimum_should_match percentage cannot exceed 100%, got: $value");
            }
        }
        
        $this->query['query']['bool']['minimum_should_match'] = $value;
        return $this;
    }

    public function filter(string|array $field, string|array|null $value = null): self {
        if ($value === null && !is_array($field)) {
            throw new \InvalidArgumentException('Filter value cannot be null for field: ' . $field);
        }
        
        if (!is_array($field)) {
            $this->validateFieldName($field);
            $this->validateValue($value);
        }
        
        $condition = is_array($field) ? $field : self::termClause($field, $value);
        
        // Verbose logging
        if (!is_array($field)) {
            $this->addVerboseLog("Added term filter on field '{$field}' for value '{$value}'");
        } else {
            $this->addVerboseLog("Added complex filter condition");
        }
        
        return $this->addFilter($condition);
    }

    public function match(string $field, string $value): self {
        $this->validateFieldName($field);
        $this->validateValue($value);
        
        if (empty(trim($value))) {
            trigger_error('Empty string provided for match query on field: ' . $field . '. This may return unexpected results.', E_USER_WARNING);
        }
        
        // Verbose logging
        $this->addVerboseLog("Added match query on field '{$field}' for value '{$value}' - text will be analyzed for better search relevance");
        
        return $this->must(self::matchClause($field, $value));
    }

    public function matchBoost(string $field, string $value, float $boost): self {
        $this->validateFieldName($field);
        $this->validateValue($value);
        $this->validateValue($boost);
        
        if (empty(trim($value))) {
            trigger_error('Empty string provided for matchBoost query on field: ' . $field . '. This may return unexpected results.', E_USER_WARNING);
        }
        return $this->must(self::matchBoostClause($field, $value, $boost));
    }

    public function termBoost(string $field, string $value, float $boost): self {
        $this->validateFieldName($field);
        $this->validateValue($value);
        $this->validateValue($boost);
        
        return $this->must(self::termBoostClause($field, $value, $boost));
    }

    public function range(string $field, string $operator, $value): self {
        // Basic validation
        if (is_numeric($value) && !is_finite($value)) {
            throw new \InvalidArgumentException('Infinite and NaN values are not supported in Elasticsearch queries');
        }
        
        $operator = strtolower($operator);
        if (!in_array($operator, $this->validRangeOperators, true)) {
            throw new \InvalidArgumentException("Invalid operator: '$operator'. Use: " . implode(', ', $this->validRangeOperators));
        }
        
        // Verbose logging
        $this->addVerboseLog("Added range filter on field '{$field}' with condition '{$operator}' and value '{$value}'");
        
        return $this->addFilter(self::rangeClause($field, $operator, $value));
    }

    public function rangeBoost(string $field, string $operator, $value, float $boost): self {
        $this->validateFieldName($field);
        $this->validateValue($value);
        $this->validateValue($boost);
        
        $operator = strtolower($operator);
        if (!in_array($operator, $this->validRangeOperators, true)) {
            throw new \InvalidArgumentException("Invalid operator: '$operator'. Use: " . implode(', ', $this->validRangeOperators));
        }
        return $this->must(self::rangeBoostClause($field, $operator, $value, $boost));
    }

    public function nested(string $path, callable $callback): self {
        if (empty(trim($path))) {
            throw new \InvalidArgumentException('Nested path cannot be empty');
        }
        
        $nestedQuery = new ElasticQuery();
        $callback($nestedQuery);
        
        $nestedQueryResult = $nestedQuery->build()['query'];
        
        // Check if nested query is empty
        if (empty($nestedQueryResult) || (isset($nestedQueryResult['bool']) && empty(array_filter($nestedQueryResult['bool'])))) {
            trigger_error('Nested query callback produced an empty query. This may not be intended.', E_USER_WARNING);
        }
        
        return $this->must(self::nestedClause($path, $nestedQueryResult));
    }

    public function aggregation(string $name, array $agg): self {
        $this->validateAggregation($name, $agg);
        
        $this->query['aggs'][$name] = $agg;
        return $this;
    }

    public function sort(string $field, string|array $direction): self {
        if (is_string($direction)) {
            // Simple sorting
            $direction = strtolower($direction);
            if (!in_array($direction, $this->validSortDirections, true)) {
                throw new \InvalidArgumentException("Invalid direction: '$direction'. Use: asc or desc.");
            }
            $this->query['sort'][] = [$field => ['order' => $direction]];
            
            // Verbose logging
            $this->addVerboseLog("Added sort on field '{$field}' in '{$direction}' order");
        } else {
            // Advanced sorting (geo_distance, script, etc.)
            $this->query['sort'][] = [$field => $direction];
            
            // Verbose logging
            $this->addVerboseLog("Added advanced sort on field '{$field}' with custom configuration");
        }
        return $this;
    }

    /**
     * Adds custom sorting (for special cases like geo_distance)
     * 
     * @param array $sortConfig Full sort configuration
     * @return self For method chaining
     */
    public function addSort(array $sortConfig): self {
        $this->query['sort'][] = $sortConfig;
        return $this;
    }

    public function size(int $size): self {
        if ($size < 0) {
            throw new \InvalidArgumentException('Size must be non-negative, got: ' . $size);
        }
        if ($size > 10000) {
            $this->addPerformanceWarning('Size value ' . $size . ' exceeds Elasticsearch default limit (10000). Consider using scroll API for large result sets.');
        }
        
        // Verbose logging
        $this->addVerboseLog("Set result size limit to {$size} documents");
        
        $this->query['size'] = $size;
        return $this;
    }

    public function from(int $offset): self {
        if ($offset < 0) {
            throw new \InvalidArgumentException('From must be non-negative, got: ' . $offset);
        }
        
        // Check for deep pagination
        $currentSize = $this->query['size'] ?? 10; // Default ES size
        if (($offset + $currentSize) > 10000) {
            $this->addPerformanceWarning('Deep pagination detected (from: ' . $offset . ', size: ' . $currentSize . '). Total exceeds 10000. Consider using search_after or scroll API for better performance.');
        }
        
        // Verbose logging
        $this->addVerboseLog("Set result offset to {$offset} (will skip first {$offset} documents)");
        
        $this->query['from'] = $offset;
        return $this;
    }

    public function highlight(array $fields, string $preTag = '<em>', string $postTag = '</em>'): self {
        $this->validateNonEmptyArray($fields, 'Highlight fields array');
        
        $highlightFields = [];
        foreach ($fields as $field) {
            $this->validateFieldName($field);
            $highlightFields[$field] = new \stdClass();
        }
        $this->query['highlight'] = [
            'pre_tags' => [$preTag],
            'post_tags' => [$postTag],
            'fields' => $highlightFields
        ];
        return $this;
    }

    public function scriptScore(string $script): self {
        $this->validateScript($script);
        
        $originalQuery = $this->query['query'];
        $this->query['query'] = [
            'script_score' => [
                'query' => $originalQuery,
                'script' => ['source' => $script]
            ]
        ];
        return $this;
    }

    public function exists(string $field): self {
        $this->validateFieldName($field);
        return $this->must(self::existsClause($field));
    }

    public function terms(string $field, array $values): self {
        $this->validateFieldName($field);
        $this->validateNonEmptyArray($values, 'Terms values array');
        
        // Validate each value in the array
        foreach ($values as $value) {
            $this->validateValue($value);
        }
        
        return $this->filter(self::termsClause($field, $values));
    }

    public function prefix(string $field, string $value): self {
        $this->validateFieldName($field);
        $this->validateValue($value);
        
        // Validate that prefix value is not empty
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('Prefix value cannot be empty');
        }
        
        return $this->must(self::prefixClause($field, $value));
    }

    public function wildcard(string $field, string $pattern): self {
        $this->validateFieldName($field);
        $this->validateValue($pattern);
        
        // Validate that pattern is not empty
        if (empty(trim($pattern))) {
            throw new \InvalidArgumentException('Wildcard pattern cannot be empty');
        }
        
        // Check for performance warnings
        $this->checkPerformanceWarnings('wildcard', ['value' => $pattern]);
        
        // Verbose logging
        $this->addVerboseLog("Added wildcard query on field '{$field}' with pattern '{$pattern}'");
        
        return $this->must(self::wildcardClause($field, $pattern));
    }

    public function regexp(string $field, string $pattern): self {
        $this->validateFieldName($field);
        $this->validateValue($pattern);
        
        // Validate that pattern is not empty
        if (empty(trim($pattern))) {
            throw new \InvalidArgumentException('Regular expression pattern cannot be empty');
        }
        
        // Check for performance warnings
        $this->checkPerformanceWarnings('regex', ['pattern' => $pattern]);
        
        // Verbose logging
        $this->addVerboseLog("Added regex query on field '{$field}' with pattern '{$pattern}' - consider performance impact on large datasets");
        
        return $this->must(self::regexpClause($field, $pattern));
    }

    public function fuzzy(string $field, string $value, $fuzziness = 'AUTO'): self {
        $this->validateFieldName($field);
        $this->validateValue($value);
        
        // Validate that fuzzy value is not empty
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('Fuzzy search value cannot be empty');
        }
        
        // Validate fuzziness parameter
        if ($fuzziness !== 'AUTO' && !is_int($fuzziness) && !is_string($fuzziness)) {
            throw new \InvalidArgumentException('Fuzziness must be "AUTO", an integer, or a string');
        }
        
        // Check for performance warnings
        $this->checkPerformanceWarnings('fuzzy', ['value' => $value, 'fuzziness' => $fuzziness]);
        
        // Verbose logging
        $this->addVerboseLog("Added fuzzy query on field '{$field}' for value '{$value}' with fuzziness '{$fuzziness}' - allows character differences for typo tolerance");
        
        return $this->must(self::fuzzyClause($field, $value, $fuzziness));
    }

    /**
     * Static methods to create reusable query fragments
     * Useful for use inside should(), must(), mustNot(), etc.
     */
    
    // ================================================================
    // STATIC CLAUSE BUILDERS
    // ================================================================
    
    /**
     * Creates a match fragment for use in compositions
     * 
     * @param string $field Field name
     * @param string|array $value Simple value or array with 'query' and optionally 'boost', 'operator', etc.
     * @return array Match query fragment
     * 
     * @example
     * ElasticQuery::matchClause('title', 'PHP')
     * ElasticQuery::matchClause('content', ['query' => 'Laravel', 'boost' => 1.5])
     */
    public static function matchClause(string $field, string|array $value): array {
        // Only cache simple string values for better cache efficiency
        if (is_string($value)) {
            $cacheKey = "match:$field:$value";
            if (isset(self::$clauseCache[$cacheKey])) {
                return self::$clauseCache[$cacheKey];
            }
            
            $clause = ['match' => [$field => $value]];
            self::cacheClause($cacheKey, $clause);
            return $clause;
        }
        
        // For array values, don't cache due to complexity
        return ['match' => [$field => $value]];
    }

    /**
     * Creates a match fragment with boost for use in compositions
     * 
     * @param string $field Field name  
     * @param string $value Value to search
     * @param float $boost Relevance multiplier
     * @return array Match query fragment with boost
     */
    public static function matchBoostClause(string $field, string $value, float $boost): array {
        return self::matchClause($field, ['query' => $value, 'boost' => $boost]);
    }

    /**
     * Creates a term fragment for use in compositions
     * 
     * @param string $field Field name
     * @param string $value Exact value to search
     * @return array Term query fragment
     */
    public static function termClause(string $field, string $value): array {
        $cacheKey = "term:$field:$value";
        if (isset(self::$clauseCache[$cacheKey])) {
            return self::$clauseCache[$cacheKey];
        }
        
        $clause = ['term' => [$field => $value]];
        self::cacheClause($cacheKey, $clause);
        return $clause;
    }

    /**
     * Creates a range fragment for use in compositions
     * 
     * @param string $field Field name
     * @param string $operator Range operator (gte, lte, gt, lt)
     * @param mixed $value Value for comparison
     * @return array Range query fragment
     */
    public static function rangeClause(string $field, string $operator, $value): array {
        return ['range' => [$field => [$operator => $value]]];
    }

    /**
     * Creates an exists fragment for use in compositions
     * 
     * @param string $field Field name
     * @return array Exists query fragment
     */
    public static function existsClause(string $field): array {
        return ['exists' => ['field' => $field]];
    }

    /**
     * Creates a prefix fragment for use in compositions
     * 
     * @param string $field Field name
     * @param string $value Prefix to search
     * @return array Prefix query fragment
     */
    public static function prefixClause(string $field, string $value): array {
        return ['prefix' => [$field => $value]];
    }

    /**
     * Creates a wildcard fragment for use in compositions
     * 
     * @param string $field Field name
     * @param string $pattern Wildcard pattern (* and ?)
     * @return array Wildcard query fragment
     */
    public static function wildcardClause(string $field, string $pattern): array {
        return ['wildcard' => [$field => $pattern]];
    }

    /**
     * Creates a regexp fragment for use in compositions
     * 
     * @param string $field Field name
     * @param string $pattern Regular expression
     * @return array Regexp query fragment
     */
    public static function regexpClause(string $field, string $pattern): array {
        return ['regexp' => [$field => $pattern]];
    }

    /**
     * Creates a terms fragment for use in compositions
     * 
     * @param string $field Field name
     * @param array $values Array of values to search
     * @return array Terms query fragment
     */
    public static function termsClause(string $field, array $values): array {
        return ['terms' => [$field => $values]];
    }

    /**
     * Creates a term fragment with boost for use in compositions
     * 
     * @param string $field Field name
     * @param string $value Exact value to search
     * @param float $boost Relevance multiplier
     * @return array Term query fragment with boost
     */
    public static function termBoostClause(string $field, string $value, float $boost): array {
        return [
            'term' => [
                $field => [
                    'value' => $value,
                    'boost' => $boost
                ]
            ]
        ];
    }

    /**
     * Creates a range fragment with boost for use in compositions
     * 
     * @param string $field Field name
     * @param string $operator Range operator (gte, lte, gt, lt)
     * @param mixed $value Value for comparison
     * @param float $boost Relevance multiplier
     * @return array Range query fragment with boost
     */
    public static function rangeBoostClause(string $field, string $operator, $value, float $boost): array {
        return [
            'range' => [
                $field => [
                    $operator => $value,
                    'boost' => $boost
                ]
            ]
        ];
    }

    /**
     * Creates a nested query fragment for use in compositions
     * 
     * @param string $path Nested object path
     * @param array $query Internal query to apply to the nested object
     * @return array Nested query fragment
     */
    public static function nestedClause(string $path, array $query): array {
        return [
            'nested' => [
                'path' => $path,
                'query' => $query
            ]
        ];
    }

    /**
     * Creates a fuzzy fragment for use in compositions
     * 
     * @param string $field Field name
     * @param string $value Value to search with fuzzy matching
     * @param string|int $fuzziness Fuzziness level ('AUTO', integer, or string)
     * @return array Fuzzy query fragment
     */
    public static function fuzzyClause(string $field, string $value, $fuzziness = 'AUTO'): array {
        $fuzzyClause = ['fuzzy' => [$field => ['value' => $value]]];
        
        if ($fuzziness !== 'AUTO') {
            $fuzzyClause['fuzzy'][$field]['fuzziness'] = $fuzziness;
        } else {
            $fuzzyClause['fuzzy'][$field]['fuzziness'] = 'AUTO';
        }
        
        return $fuzzyClause;
    }

    public function build(): array {
        return $this->query;
    }

    public function toDSL(int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): string {
        return json_encode($this->build(), $flags);
    }

    /**
     * Get basic query analysis for debugging
     * 
     * @return array Simple analysis of query structure
     */
    public function analyze(): array {
        $built = $this->build();
        $analysis = [
            'has_search' => false,
            'clause_count' => 0,
            'has_aggregations' => false,
            'has_sorting' => false,
            'estimated_complexity' => 'low'
        ];

        // Check for search queries
        if (isset($built['query']['bool']['must'])) {
            foreach ($built['query']['bool']['must'] as $clause) {
                if (isset($clause['match']) || isset($clause['multi_match'])) {
                    $analysis['has_search'] = true;
                    break;
                }
            }
        }

        // Count total clauses
        if (isset($built['query']['bool'])) {
            foreach (['must', 'should', 'filter', 'must_not'] as $type) {
                if (isset($built['query']['bool'][$type])) {
                    $analysis['clause_count'] += count($built['query']['bool'][$type]);
                }
            }
        }

        // Check for aggregations
        $analysis['has_aggregations'] = isset($built['aggs']);

        // Check for sorting
        $analysis['has_sorting'] = isset($built['sort']);

        // Estimate complexity
        if ($analysis['clause_count'] > 5 || $analysis['has_aggregations']) {
            $analysis['estimated_complexity'] = 'medium';
        }
        if ($analysis['clause_count'] > 10) {
            $analysis['estimated_complexity'] = 'high';
        }

        return $analysis;
    }

    protected function addFilter(array $condition): self {
        $this->query['query']['bool']['filter'][] = $condition;
        return $this;
    }

    /**
     * Eloquent-style methods for better usability
     */

    // ================================================================
    // WHERE CLAUSE METHODS
    // ================================================================

    public function where(string $field, $operator, $value = null): self {
        $this->validateFieldName($field);
        
        // If only 2 parameters, assume equality
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        // Validate that value is not null for comparison operations
        if ($value === null) {
            throw new \InvalidArgumentException('Where clause value cannot be null for field: ' . $field);
        }
        
        $this->validateValue($value);
        
        // Check for potential logical conflicts
        $this->checkForLogicalConflicts($field, $operator, $value);

        return match($operator) {
            '=' => $this->filter($field, $value),
            '!=' => $this->mustNot(self::termClause($field, $value)),
            '>' => $this->filter(self::rangeClause($field, 'gt', $value)),
            '>=' => $this->filter(self::rangeClause($field, 'gte', $value)),
            '<' => $this->filter(self::rangeClause($field, 'lt', $value)),
            '<=' => $this->filter(self::rangeClause($field, 'lte', $value)),
            default => throw new \InvalidArgumentException("Unsupported operator: '$operator'. Use: =, !=, >, >=, <, <=")
        };
    }

    public function whereIn(string $field, array $values): self {
        $this->validateFieldName($field);
        $this->validateNonEmptyArray($values, 'WhereIn values array');
        
        foreach ($values as $value) {
            $this->validateValue($value);
        }
        
        return $this->filter(self::termsClause($field, $values));
    }

    public function whereNotIn(string $field, array $values): self {
        $this->validateFieldName($field);
        $this->validateNonEmptyArray($values, 'WhereNotIn values array');
        
        foreach ($values as $value) {
            $this->validateValue($value);
        }
        
        return $this->mustNot(self::termsClause($field, $values));
    }

    public function whereBetween(string $field, array $values): self {
        $this->validateFieldName($field);
        
        if (count($values) !== 2) {
            throw new \InvalidArgumentException('whereBetween requires exactly 2 values: [min, max]');
        }
        
        [$min, $max] = $values;
        $this->validateValue($min);
        $this->validateValue($max);
        
        // Check if min > max (logical error)
        if (is_numeric($min) && is_numeric($max) && $min > $max) {
            throw new \InvalidArgumentException("whereBetween: minimum value ($min) cannot be greater than maximum value ($max)");
        }
        
        return $this->filter(self::rangeClause($field, 'gte', $min))
                   ->filter(self::rangeClause($field, 'lte', $max));
    }

    public function whereNotNull(string $field): self {
        $this->validateFieldName($field);
        return $this->must(self::existsClause($field));
    }

    public function whereNull(string $field): self {
        $this->validateFieldName($field);
        return $this->mustNot(self::existsClause($field));
    }

    // ================================================================
    // ELOQUENT-STYLE ORDER & PAGINATION METHODS
    // ================================================================

    public function orderBy(string $column, string $direction = 'asc'): self {
        return $this->sort($column, $direction);
    }

    public function orderByDesc(string $column): self {
        return $this->sort($column, 'desc');
    }

    public function latest(string $column = 'created_at'): self {
        return $this->orderByDesc($column);
    }

    public function oldest(string $column = 'created_at'): self {
        return $this->orderBy($column, 'asc');
    }

    public function paginate(int $perPage = 15, int $page = 1): self {
        if ($page < 1) {
            throw new \InvalidArgumentException('Page number must be greater than 0');
        }
        
        if ($perPage < 1) {
            throw new \InvalidArgumentException('Items per page must be greater than 0');
        }

        $offset = ($page - 1) * $perPage;
        
        // Check for performance warnings
        $this->checkPerformanceWarnings('pagination', [
            'from' => $offset,
            'size' => $perPage
        ]);
        
        // Verbose logging
        $this->addVerboseLog("Set pagination to page {$page} with {$perPage} items per page (offset: {$offset})");

        return $this->size($perPage)
                   ->from($offset);
    }

    public function limit(int $limit): self {
        return $this->size($limit);
    }

    public function offset(int $offset): self {
        return $this->from($offset);
    }

    public function take(int $count): self {
        return $this->size($count);
    }

    public function skip(int $count): self {
        return $this->from($count);
    }

    public function when($condition, callable $callback, callable $default = null): self {
        if ($condition) {
            return $callback($this);
        } elseif ($default) {
            return $default($this);
        }
        return $this;
    }

    public function unless($condition, callable $callback, callable $default = null): self {
        return $this->when(!$condition, $callback, $default);
    }

    // ================================================================
    // FULL-TEXT SEARCH METHODS
    // ================================================================

    /**
     * Eloquent-style full-text search methods
     */

    public function search(string $query, array $fields = ['_all'], float $boost = 1.0): self {
        $this->validateValue($query);
        $this->validateNonEmptyArray($fields, 'Search fields array');
        $this->validateValue($boost);
        
        if (empty(trim($query))) {
            $this->addWarning('Empty string provided for search query. This may return unexpected results.');
        }
        
        // Check for potential performance issues
        if (strlen($query) > 1000) {
            $this->addPerformanceWarning("Very long search query (" . strlen($query) . " characters). Consider shortening for better performance.");
        }
        
        if (count($fields) > 10) {
            $this->addPerformanceWarning("Searching across many fields (" . count($fields) . "). This may impact performance on large indices.");
        }
        
        // Validate each field in the fields array
        foreach ($fields as $field) {
            if (is_string($field)) {
                // Handle boosted fields like 'title^2'
                $fieldName = preg_replace('/\^[\d.]+$/', '', $field);
                $this->validateFieldName($fieldName);
            }
        }
        
        $multiMatch = [
            'multi_match' => [
                'query' => $query,
                'fields' => $fields
            ]
        ];
        
        if ($boost !== 1.0) {
            $multiMatch['multi_match']['boost'] = $boost;
        }
        
        // Verbose logging
        $this->addVerboseLog("Added multi-match search for query '{$query}' across " . count($fields) . " fields: " . implode(', ', $fields) . 
                           ($boost !== 1.0 ? " with boost {$boost}" : ""));
        
        return $this->must($multiMatch);
    }

    public function searchIn(string $field, string $query): self {
        return $this->match($field, $query);
    }

    public function searchPhrase(string $field, string $phrase): self {
        $this->validateFieldName($field);
        $this->validateValue($phrase);
        return $this->must(['match_phrase' => [$field => $phrase]]);
    }

    // ================================================================
    // PREDEFINED SCOPE METHODS
    // ================================================================

    /**
     * Common predefined scopes
     */

    public function active(): self {
        return $this->where('status', 'active');
    }

    public function published(): self {
        return $this->where('status', 'published')
                   ->where('published_at', '<=', date('Y-m-d H:i:s'));
    }

    public function recent(int $days = 30): self {
        $date = date('Y-m-d', strtotime("-$days days"));
        return $this->where('created_at', '>=', $date);
    }

    // ================================================================
    // ELOQUENT-STYLE AGGREGATION METHODS
    // ================================================================

    /**
     * Eloquent-style aggregation methods
     */

    public function count(): self {
        return $this->aggregation('total_count', [
            'value_count' => ['field' => '_id']
        ])->size(0);
    }

    public function avg(string $field): self {
        return $this->aggregation('avg_' . $field, [
            'avg' => ['field' => $field]
        ]);
    }

    public function sum(string $field): self {
        return $this->aggregation('sum_' . $field, [
            'sum' => ['field' => $field]
        ]);
    }

    public function max(string $field): self {
        return $this->aggregation('max_' . $field, [
            'max' => ['field' => $field]
        ]);
    }

    public function min(string $field): self {
        return $this->aggregation('min_' . $field, [
            'min' => ['field' => $field]
        ]);
    }

    /**
     * Controls which fields are returned in the response
     * 
     * @param array|bool $fields Array of field names to include, or false to exclude all fields
     * @return self For method chaining
     */
    public function source(array|bool $fields): self {
        if (is_array($fields)) {
            $this->validateNonEmptyArray($fields, 'Source fields array');
            foreach ($fields as $field) {
                $this->validateFieldName($field);
            }
        }
        
        $this->query['_source'] = $fields;
        return $this;
    }

    /**
     * Sets a timeout for the query
     * 
     * @param string $timeout Timeout value (e.g., '5s', '1m', '30s')
     * @return self For method chaining
     */
    public function timeout(string $timeout): self {
        $this->validateValue($timeout);
        
        // Basic timeout format validation
        if (!preg_match('/^\d+[smhd]$/', $timeout)) {
            throw new \InvalidArgumentException("Invalid timeout format: '$timeout'. Use format like '30s', '5m', '1h'");
        }
        
        $this->query['timeout'] = $timeout;
        return $this;
    }

    /**
     * Adds a geo_distance query
     * 
     * @param string $field The field containing geo-point data
     * @param array|string $location The center point as array ['lat' => x, 'lon' => y] or string "lat,lon"
     * @param string $distance The search radius (e.g., '10km', '5mi')
     * @return self For method chaining
     */
    public function geoDistance(string $field, array|string $location, string $distance): self {
        $this->validateFieldName($field);
        $this->validateValue($distance);
        
        // Validate distance format
        if (!preg_match('/^\d+(\.\d+)?(km|m|mi|yd|ft|in|mm|cm|nmi)$/', $distance)) {
            throw new \InvalidArgumentException("Invalid distance format: '$distance'. Use format like '5km', '100m', '2mi'");
        }
        
        // Validate coordinates if array format
        if (is_array($location)) {
            if (!isset($location['lat'], $location['lon'])) {
                throw new \InvalidArgumentException('Location array must contain lat and lon keys');
            }
            $this->validateGeoCoordinates($location['lat'], $location['lon']);
        } else {
            // Validate string format (lat,lon)
            if (!preg_match('/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/', $location)) {
                throw new \InvalidArgumentException("Invalid location string format: '$location'. Use 'lat,lon' format");
            }
        }
        
        return $this->must([
            'geo_distance' => [
                'distance' => $distance,
                $field => $location
            ]
        ]);
    }

    /**
     * Helper method to cache clause fragments
     * 
     * @param string $key Cache key
     * @param array $clause Clause to cache
     */
    protected static function cacheClause(string $key, array $clause): void {
        // Simple LRU-like cache management
        if (self::$cacheSize >= self::$maxCacheSize) {
            // Remove first few entries to make room
            $keysToRemove = array_slice(array_keys(self::$clauseCache), 0, 20);
            foreach ($keysToRemove as $keyToRemove) {
                unset(self::$clauseCache[$keyToRemove]);
                self::$cacheSize--;
            }
        }
        
        self::$clauseCache[$key] = $clause;
        self::$cacheSize++;
    }

    // ===================================================================
    // DEVELOPER EXPERIENCE ENHANCEMENT METHODS
    // ===================================================================

    /**
     * Enable or disable verbose mode for detailed query building explanations
     * 
     * When enabled, the query builder will log detailed explanations of what
     * each method call does and how it affects the final query structure.
     * 
     * @param bool $enabled Whether to enable verbose mode
     * @return self For method chaining
     * 
     * @example
     * $query = (new ElasticQuery())
     *     ->verbose()
     *     ->search('laravel tutorial')
     *     ->where('status', 'published');
     * 
     * echo $query->getVerboseLog(); // Shows detailed explanations
     */
    public function verbose(bool $enabled = true): self {
        $this->verboseMode = $enabled;
        if ($enabled) {
            $this->addVerboseLog('Verbose mode enabled - detailed query building explanations will be logged');
        } else {
            $this->buildLog = [];
            $this->warnings = [];
        }
        return $this;
    }

    /**
     * Get the verbose build log showing detailed explanations of query construction
     * 
     * @return string Formatted log of query building steps
     */
    public function getVerboseLog(): array {
        return $this->buildLog;
    }

    /**
     * Get all warnings generated during query building
     * 
     * @return array Array of warning messages
     */
    public function getWarnings(): array {
        return array_merge($this->warnings, $this->performanceWarnings);
    }

    /**
     * Get just the performance warnings
     * 
     * @return array Array of performance warning messages
     */
    public function getPerformanceWarnings(): array {
        return $this->performanceWarnings;
    }

    /**
     * Translate common Elasticsearch errors into friendly, actionable messages
     * 
     * @param string $error The raw error from Elasticsearch
     * @return string Friendly, actionable error message
     */
    public function translateError(string $error): string {
        $patterns = [
            // Field mapping errors
            '/No mapping found for \[([^\]]+)\]/' => 'Field "$1" doesn\'t exist in your index. Check your field name spelling or ensure the field is properly mapped.',
            
            // Type mismatches
            '/Cannot parse \[([^\]]+)\].*as.*\[([^\]]+)\]/' => 'The value "$1" cannot be used as a $2. Check your data type - you might be sending text to a number field.',
            
            // Search query parsing errors
            '/\[bool\] malformed query/' => 'Your search query structure is invalid. Check that you\'re using the correct combination of must/should/must_not conditions.',
            
            '/Failed to parse query.*Expected.*\[([^\]]+)\]/' => 'Query syntax error near "$1". Check your query structure and ensure all brackets and quotes are properly closed.',
            
            // Aggregation errors
            '/Unknown aggregation type \[([^\]]+)\]/' => 'The aggregation type "$1" is not supported. Use one of: terms, date_histogram, histogram, range, stats, avg, sum, min, max, etc.',
            
            '/Fielddata is disabled on text fields/' => 'You\'re trying to aggregate or sort on a text field. Use the .keyword version instead (e.g., "category.keyword" instead of "category").',
            
            // Index errors
            '/index_not_found_exception.*no such index \[([^\]]+)\]/' => 'Index "$1" doesn\'t exist. Make sure your index name is correct and the index has been created.',
            
            // Size/timeout errors
            '/Result window is too large.*from \+ size must be <= (\d+)/' => 'You\'re trying to fetch too many results. Use pagination with smaller page sizes or consider using search_after for deep pagination.',
            
            // Timeout errors
            '/timeout_exception.*search phase took longer than/' => 'Your search timed out during execution. Optimize your query by reducing complexity, using filters instead of queries where possible, or increasing the timeout value.',
            
            '/search_phase_execution_exception.*timeout/' => 'Search operation timed out. Try simplifying your query, reducing the number of fields searched, or using more specific filters to narrow results.',
            
            '/request_timeout_exception/' => 'The request timed out waiting for a response. This usually indicates a complex query or cluster performance issues. Consider breaking down your query or optimizing your index.',
            
            '/Request timeout after/' => 'Request exceeded the configured timeout. For complex queries, consider increasing the timeout value or optimizing the query structure.',
            
            '/timeout/' => 'Operation timed out. Try simplifying your query, using more specific filters, or increasing the timeout value for complex operations.',
            
            '/circuit_breaking_exception.*request.*exceeded.*bytes/' => 'Your query is too large and was rejected to prevent memory issues. Try reducing the size parameter or simplifying your query.',
            
            // Security/permission errors
            '/security_exception.*action \[([^\]]+)\].*is unauthorized/' => 'You don\'t have permission to perform "$1". Contact your administrator to get the necessary permissions.',
            
            // Routing errors
            '/routing_missing_exception/' => 'This document requires routing information. Add a routing parameter to your query.',
            
            // Version conflicts
            '/version_conflict_engine_exception/' => 'Document was modified by another process. This usually happens with concurrent updates - consider retrying your operation.',
            
            // Script errors
            '/script_exception.*runtime error/' => 'Your script has a runtime error. Check your script syntax and ensure all referenced fields exist.',
            
            // Connection/cluster errors
            '/no_shard_available_exception/' => 'No healthy shards available to process your request. The cluster might be experiencing issues.',
            
            '/cluster_block_exception/' => 'The cluster is blocking operations. This might be due to disk space issues or cluster configuration.',
        ];

        foreach ($patterns as $pattern => $friendlyMessage) {
            if (preg_match($pattern, $error, $matches)) {
                // Replace placeholders with captured groups
                $message = $friendlyMessage;
                for ($i = 1; $i < count($matches); $i++) {
                    $message = str_replace('$' . $i, $matches[$i], $message);
                }
                
                return $message . "\n\nOriginal error: " . $error;
            }
        }

        // If no pattern matches, provide a generic helpful response
        return "We encountered an Elasticsearch error. Here are some common solutions:\n\n" .
               "• Check your field names are spelled correctly\n" .
               "• Ensure your data types match (numbers for numeric fields, etc.)\n" .
               "• Verify your index exists and has the expected mapping\n" .
               "• For aggregations, use .keyword fields for text data\n\n" .
               "Original error: " . $error;
    }

    /**
     * Add an entry to the verbose build log
     * 
     * @param string $message Log message
     */
    protected function addVerboseLog(string $message): void {
        if ($this->verboseMode) {
            $this->buildLog[] = $message;
        }
    }

    /**
     * Add a warning message
     * 
     * @param string $message Warning message
     */
    protected function addWarning(string $message): void {
        $this->warnings[] = $message;
        $this->addVerboseLog("⚠️  WARNING: {$message}");
    }

    /**
     * Add a performance warning
     * 
     * @param string $message Performance warning message
     */
    protected function addPerformanceWarning(string $message): void {
        $this->performanceWarnings[] = $message;
        $this->addVerboseLog("🐌 PERFORMANCE: {$message}");
    }

    /**
     * Check for potential performance issues and add warnings
     * 
     * @param string $operation The operation being performed
     * @param array $params Parameters for the operation
     */
    protected function checkPerformanceWarnings(string $operation, array $params = []): void {
        switch ($operation) {
            case 'wildcard':
                if (isset($params['value']) && is_string($params['value']) && str_starts_with($params['value'], '*')) {
                    $this->addPerformanceWarning("Leading wildcard query (*{$params['value']}) will scan all terms and can be very slow on large indices.");
                }
                break;
                
            case 'regex':
                $this->addPerformanceWarning("Regex queries can be slow on large datasets. Consider using prefix, wildcard, or fuzzy queries instead.");
                break;
                
            case 'pagination':
                if (isset($params['from'], $params['size'])) {
                    $offset = $params['from'];
                    $limit = $params['size'];
                    if ($offset > 10000) {
                        $this->addPerformanceWarning("Deep pagination (offset: {$offset}) can be slow. Consider using search_after for better performance.");
                    }
                    if ($limit > 100) {
                        $this->addPerformanceWarning("Large page size ({$limit}) may impact performance. Consider smaller page sizes.");
                    }
                }
                break;
                
            case 'sort_script':
                $this->addPerformanceWarning("Script-based sorting can be slow on large datasets. Consider using field-based sorting when possible.");
                break;
                
            case 'nested_aggregation':
                if (isset($params['depth']) && $params['depth'] > 3) {
                    $this->addPerformanceWarning("Deep nested aggregations (depth: {$params['depth']}) can impact performance significantly.");
                }
                break;
        }
    }
}

