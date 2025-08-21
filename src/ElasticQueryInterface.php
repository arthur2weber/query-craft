<?php

namespace Arthur2weber\QueryCraft;

/**
 * QueryCraft DSL Query Builder Interface
 * 
 * A fluent, Eloquent-inspired query builder for multiple DSL engines that makes complex searches simple and intuitive.
 * This interface defines the contract for building DSL queries with familiar Laravel-like syntax.
 * 
 * @package Arthur2weber\QueryCraft
 * @author Arthur Weber<arthur2weber@gmail.com>
 * @version 1.0.0
 * 
 * @example Basic usage
 * $query = new ElasticQuery();
 * $results = $query
 *     ->search('Laravel tutorial', ['title^2', 'content'])
 *     ->where('status', 'published')
 *     ->recent(30)
 *     ->paginate(15);
 * 
 * @example E-commerce search
 * $products = $query
 *     ->search('smartphone', ['name^3', 'description'])
 *     ->whereBetween('price', [200, 1000])
 *     ->where('in_stock', true)
 *     ->orderByDesc('rating')
 *     ->limit(20);
 */
interface ElasticQueryInterface 
{
    /**
     * Creates a nested query clause for use in compositions
     * 
     * @param string $path Path to the nested object
     * @param array $query Internal query to apply to the nested object
     * @return array Nested query fragment
     * 
     * @example
     * $clause = ElasticQuery::nestedClause('comments', [
     *     'match' => ['comments.text' => 'excellent']
     * ]);
     * $query->must($clause);
     */
    public static function nestedClause(string $path, array $query): array;

    /**
     * Adds a condition that must be true (logical AND)
     * 
     * Equivalent to the AND operator in boolean logic. All returned
     * documents must satisfy this condition, in addition to any other
     * must condition already added.
     * 
     * @param array $condition Array with the condition to apply
     * @return self For method chaining
     * 
     * @example
     * $query->must(ElasticQuery::termClause('status', 'active'))
     *        ->must(ElasticQuery::rangeClause('age', 'gte', 18));
     */
    public function must(array $condition): self;

    /**
     * Adds a condition that should be true (logical OR)
     * 
     * At least one of the should() conditions must be satisfied. Used to
     * implement OR logic or to add optional boost criteria.
     * 
     * @param array $condition Array containing the Elasticsearch condition
     * @param string|int|null $minimumShouldMatch Minimum number of should conditions that must be satisfied
     * @return self For method chaining
     *
     * @example
     * $query->should(ElasticQuery::matchClause('title', 'php'))
     *        ->should(ElasticQuery::matchClause('description', 'php'), 1); // At least 1 must be satisfied
     */
    public function should(array $condition, string|int|null $minimumShouldMatch = null): self;

    /**
     * Adds a condition that must not be true (logical NOT)
     * 
     * Excludes documents that match the specified condition.
     * Equivalent to the NOT operator.
     * 
     * @param array $condition Array containing the Elasticsearch condition
     * @return self For method chaining
     * 
     * @example
     * $query->mustNot(ElasticQuery::termClause('status', 'deleted'))
     *        ->mustNot(ElasticQuery::rangeClause('age', 'lt', 18));
     */
    public function mustNot(array $condition): self;

    /**
     * Sets the minimum number of should conditions that must be satisfied
     * 
     * Controls how many of the should() conditions must be true for a
     * document to be considered a match. Accepts integers or percentages as string.
     * 
     * @param string|int $value Minimum number or percentage (e.g.: 2, "75%", "2<75%")
     * @return self For method chaining
     * 
     * @example
     * $query->should(ElasticQuery::matchClause('title', 'php'))
     *        ->should(ElasticQuery::matchClause('content', 'laravel'))
     *        ->should(ElasticQuery::termClause('featured', true))
     *        ->minimumShouldMatch(2); // At least 2 out of 3 conditions must be satisfied
     */
    public function minimumShouldMatch(string|int $value): self;

    /**
     * Adds an exact term filter
     * 
     * Filters documents that contain the exact value specified in the field.
     * Does not affect document scoring (does not influence relevance).
     * 
     * @param string|array $field Field name or array with complete condition
     * @param string|array|null $value Value to filter (ignored if $field is array)
     * @return self For method chaining
     * 
     * @example
     * $query->filter('status', 'published')
     *        ->filter(['terms' => ['category' => ['tech', 'news']]]);
     */
    public function filter(string|array $field, string|array|null $value = null): self;

    /**
     * Adds a text match search
     * 
     * Performs a text search with full analysis (tokenization,
     * stemming, etc.). Ideal for searching in free text fields.
     * 
     * @param string $field Field name to search
     * @param string $value Text to search for
     * @return self For method chaining
     * 
     * @example
     * $query->match('title', 'web development')
     *        ->match('content', 'PHP Laravel');
     */
    public function match(string $field, string $value): self;

    /**
     * Adds a text match search with boost
     * 
     * Similar to match(), but allows setting a boost value to
     * increase or decrease the relevance of this condition in the final score.
     * 
     * @param string $field Field name to search
     * @param string $value Text to search for
     * @param float $boost Relevance multiplier (1.0 = neutral, >1.0 = more relevant, <1.0 = less relevant)
     * @return self For method chaining
     * 
     * @example
     * $query->matchBoost('title', 'PHP', 2.0)      // Title has double weight
     *        ->matchBoost('tags', 'PHP', 1.5);     // Tags have 1.5x weight
     */
    public function matchBoost(string $field, string $value, float $boost): self;

    /**
     * Adds an exact term with boost
     * 
     * Searches for the exact value in the specified field, without text analysis,
     * with the possibility to apply boost to relevance.
     * 
     * @param string $field Field name to search
     * @param string $value Exact value to search for
     * @param float $boost Relevance multiplier
     * @return self For method chaining
     * 
     * @example
     * $query->termBoost('category', 'programming', 2.0)
     *        ->termBoost('priority', 'high', 1.8);
     */
    public function termBoost(string $field, string $value, float $boost): self;

    /**
     * Adds a range condition (interval)
     * 
     * Filters documents where the field value is within a specified range.
     * Useful for dates, numbers, and other sortable values.
     * 
     * @param string $field Field name to apply the range
     * @param string $operator Comparison operator: 'gte', 'lte', 'gt', 'lt'
     * @param mixed $value Value for comparison
     * @return self For method chaining
     * 
     * @example
     * $query->range('price', 'gte', 100)           // Price >= 100
     *        ->range('price', 'lte', 500)          // Price <= 500
     *        ->range('created_at', 'gte', '2024-01-01');
     */
    public function range(string $field, string $operator, $value): self;

    /**
     * Adds a range condition with boost
     * 
     * Similar to range(), but with the possibility to apply boost
     * to influence the score of documents that meet the criteria.
     * 
     * @param string $field Field name to apply the range
     * @param string $operator Comparison operator: 'gte', 'lte', 'gt', 'lt'
     * @param mixed $value Value for comparison
     * @param float $boost Relevance multiplier
     * @return self For method chaining
     * 
     * @example
     * $query->rangeBoost('rating', 'gte', 4.0, 1.5)  // High rating with boost
     *        ->rangeBoost('views', 'gte', 1000, 1.2); // Many views
     */
    public function rangeBoost(string $field, string $operator, $value, float $boost): self;

    /**
     * Adds a nested query
     * 
     * Allows querying nested objects within a main document.
     * Accepts a callback that receives a new builder to build the inner query fluently.
     * 
     * @param string $path Path to the nested object
     * @param callable $callback Function that receives an ElasticQuery to build the inner query
     * @return self For method chaining
     * 
     * @example
     * $query->nested('comments', function($q) {
     *     $q->range('comments.rating', 'gte', 4)
     *       ->match('comments.text', 'excellent');
     * })->nested('instructor', function($q) {
     *     $q->filter('instructor.verified', true)
     *       ->range('instructor.experience_years', 'gte', 5);
     * });
     */
    public function nested(string $path, callable $callback): self;

    /**
     * Adds an aggregation to the query
     * 
     * Allows calculating metrics, groupings, and statistics on the data.
     * Aggregations are executed on the set of returned documents.
     * 
     * @param string $name Aggregation name for identification in the results
     * @param array $agg Elasticsearch aggregation configuration
     * @return self For method chaining
     * 
     * @example
     * $query->aggregation('avg_price', [
     *     'avg' => ['field' => 'price']
     * ])->aggregation('categories', [
     *     'terms' => ['field' => 'category.keyword', 'size' => 10]
     * ]);
     */
    public function aggregation(string $name, array $agg): self;

    /**
     * Sets the result ordering
     * 
     * Specifies how documents should be ordered in the results.
     * Can be called multiple times for multi-field ordering.
     * Supports simple (asc/desc) and advanced (geo_distance, script, etc) ordering.
     * 
     * @param string $field Field name for ordering
     * @param string|array $direction Order direction ('asc'|'desc') or advanced configuration
     * @return self For method chaining
     * 
     * @example
     * $query->sort('created_at', 'desc')    // Most recent first
     *        ->sort('title.keyword', 'asc') // Then by title A-Z
     *        ->sort('_geo_distance', [      // Geo ordering
     *            'location' => ['lat' => -23.5505, 'lon' => -46.6333],
     *            'order' => 'asc',
     *            'unit' => 'km'
     *        ]);
     */
    public function sort(string $field, string|array $direction): self;

    /**
     * Sets the maximum number of results
     * 
     * Limits how many documents will be returned in the response.
     * Similar to LIMIT in SQL.
     * 
     * @param int $size Maximum number of documents to return
     * @return self For method chaining
     * 
     * @example
     * $query->size(10);  // Returns up to 10 documents
     * $query->size(100); // Returns up to 100 documents
     */
    public function size(int $size): self;

    /**
     * Sets the result offset (pagination)
     * 
     * Specifies how many documents should be skipped before starting
     * to return results. Used to implement pagination.
     * 
     * @param int $offset Number of documents to skip
     * @return self For method chaining
     * 
     * @example
     * // Page 1: from(0)->size(10)
     * // Page 2: from(10)->size(10)  
     * // Page 3: from(20)->size(10)
     * $query->from(20)->size(10);
     */
    public function from(int $offset): self;

    /**
     * Adds highlight to the specified fields
     * 
     * Highlights the found terms in text fields, wrapping them
     * with customizable HTML tags. Useful to show where the term was found.
     * 
     * @param array $fields List of fields to apply highlight
     * @param string $preTag Opening HTML tag for highlight (default: '<em>')
     * @param string $postTag Closing HTML tag for highlight (default: '</em>')
     * @return self For method chaining
     * 
     * @example
     * $query->highlight(['title', 'content'], '<mark>', '</mark>');
     * // Result: "This is a <mark>highlight</mark> example"
     */
    public function highlight(array $fields, string $preTag = '<em>', string $postTag = '</em>'): self;

    /**
     * Adds a custom scoring script
     * 
     * Allows modifying the document score using a custom script.
     * Useful for implementing complex relevance algorithms.
     * 
     * @param string $script Painless script code to calculate the score
     * @return self For method chaining
     * 
     * @example
     * $query->scriptScore("Math.log(2 + doc['popularity'].value) * _score");
     * // Multiplies the base score by the log of popularity
     */
    public function scriptScore(string $script): self;

    /**
     * Checks if a field exists
     * 
     * Filters documents that have the specified field (not null and not empty).
     * Useful to ensure required data is present.
     * 
     * @param string $field Field name to check
     * @return self For method chaining
     * 
     * @example
     * $query->exists('email')      // Only users with email
     *        ->exists('phone');    // And also have phone
     */
    public function exists(string $field): self;

    /**
     * Searches for multiple values in a field
     * 
     * Filters documents where the field contains any of the specified values.
     * Equivalent to the IN operator in SQL.
     * 
     * @param string $field Field name to search
     * @param array $values Array of possible values
     * @return self For method chaining
     * 
     * @example
     * $query->terms('status', ['active', 'pending', 'approved'])
     *        ->terms('category', ['tech', 'science']);
     */
    public function terms(string $field, array $values): self;

    /**
     * Searches by prefix
     * 
     * Finds documents where the field starts with the specified prefix.
     * Useful for autocomplete and searching codes/identifiers.
     * 
     * @param string $field Field name to search
     * @param string $value Prefix to search for
     * @return self For method chaining
     * 
     * @example
     * $query->prefix('title', 'Course of')     // "Course of PHP", "Course of Java"
     *        ->prefix('code', 'PROD-');        // "PROD-001", "PROD-002"
     */
    public function prefix(string $field, string $value): self;

    /**
     * Searches by wildcard pattern
     * 
     * Allows using wildcards (* for multiple characters, ? for one character)
     * in the search. Useful for flexible text patterns.
     * 
     * @param string $field Field name to search
     * @param string $pattern Pattern with wildcards (* and ?)
     * @return self For method chaining
     * 
     * @example
     * $query->wildcard('email', '*@gmail.com')     // Gmail emails
     *        ->wildcard('filename', '*.pdf');      // PDF files
     */
    public function wildcard(string $field, string $pattern): self;

    /**
     * Searches by regular expression
     * 
     * Allows using regular expressions to search in text fields.
     * Offers maximum flexibility for complex patterns.
     * 
     * @param string $field Field name to search
     * @param string $pattern Regular expression (Lucene syntax)
     * @return self For method chaining
     * 
     * @example
     * $query->regexp('phone', '[0-9]{2}[0-9]{4,5}-?[0-9]{4}')  // BR phone numbers
     *        ->regexp('code', 'PROD-[0-9]{3}');                // Product codes
     */
    public function regexp(string $field, string $pattern): self;

    /**
     * Searches with fuzzy matching
     * 
     * Allows finding documents even when there are character differences (typos).
     * Useful for handling user input errors and improving search tolerance.
     * 
     * @param string $field Field name to search
     * @param string $value Value to search with fuzzy matching
     * @param mixed $fuzziness Fuzziness level ('AUTO', integer 0-2, or string)
     * @return self For method chaining
     * 
     * @example
     * $query->fuzzy('title', 'javscript', 2)      // Allows 2 character differences
     *        ->fuzzy('name', 'john', 'AUTO');      // Auto fuzziness based on term length
     */
    public function fuzzy(string $field, string $value, $fuzziness = 'AUTO'): self;

    /**
     * Static methods to create reusable query fragments
     * Useful for use inside should(), must(), mustNot(), etc.
     */

    /**
     * Creates a match fragment for use in compositions
     * 
     * @param string $field Field name
     * @param string|array $value Simple value or array with 'query' and optionally 'boost', 'operator', etc.
     * @return array Match query fragment
     *
     * @example
     * $query->should(ElasticQuery::matchClause('title', 'PHP'))
     *        ->should(ElasticQuery::matchClause('content', ['query' => 'PHP', 'boost' => 1.5]));
     */
    public static function matchClause(string $field, string|array $value): array;

    /**
     * Creates a match fragment with boost for use in compositions
     * 
     * @param string $field Field name
     * @param string $value Value to search for  
     * @param float $boost Relevance multiplier
     * @return array Match query fragment with boost
     * 
     * @example
     * $query->should(ElasticQuery::matchBoostClause('title', 'Laravel', 2.0))
     *        ->should(ElasticQuery::matchBoostClause('content', 'PHP', 1.5));
     */
    public static function matchBoostClause(string $field, string $value, float $boost): array;

    /**
     * Creates a term fragment for use in compositions
     * 
     * @param string $field Field name
     * @param string $value Exact value to search for
     * @return array Term query fragment
     * 
     * @example
     * $query->must(ElasticQuery::termClause('status', 'published'))
     *        ->mustNot(ElasticQuery::termClause('type', 'draft'));
     */
    public static function termClause(string $field, string $value): array;

    /**
     * Creates a range fragment for use in compositions
     * 
     * @param string $field Field name
     * @param string $operator Operator ('gte', 'lte', 'gt', 'lt')
     * @param mixed $value Value for comparison
     * @return array Range query fragment
     */
    public static function rangeClause(string $field, string $operator, $value): array;

    /**
     * Creates an exists fragment for use in compositions
     * 
     * @param string $field Field name to check
     * @return array Exists query fragment
     */
    public static function existsClause(string $field): array;

    /**
     * Creates a prefix fragment for use in compositions
     * 
     * @param string $field Field name
     * @param string $value Prefix to search for
     * @return array Prefix query fragment
     */
    public static function prefixClause(string $field, string $value): array;

    /**
     * Creates a wildcard fragment for use in compositions
     * 
     * @param string $field Field name
     * @param string $pattern Wildcard pattern
     * @return array Wildcard query fragment
     */
    public static function wildcardClause(string $field, string $pattern): array;

    /**
     * Creates a regexp fragment for use in compositions
     * 
     * @param string $field Field name
     * @param string $pattern Regular expression
     * @return array Regexp query fragment
     */
    public static function regexpClause(string $field, string $pattern): array;

    /**
     * Creates a terms fragment for use in compositions
     * 
     * @param string $field Field name
     * @param array $values Array of values
     * @return array Terms query fragment
     */
    public static function termsClause(string $field, array $values): array;

    /**
     * Creates a term fragment with boost for use in compositions
     * 
     * @param string $field Field name
     * @param string $value Exact value to search for
     * @param float $boost Relevance multiplier
     * @return array Term query fragment with boost
     */
    public static function termBoostClause(string $field, string $value, float $boost): array;

    /**
     * Creates a range fragment with boost for use in compositions
     * 
     * @param string $field Field name
     * @param string $operator Operator ('gte', 'lte', 'gt', 'lt')
     * @param mixed $value Value for comparison
     * @param float $boost Relevance multiplier
     * @return array Range query fragment with boost
     */
    public static function rangeBoostClause(string $field, string $operator, $value, float $boost): array;

    /**
     * Builds and returns the final query
     * 
     * Compiles all query configuration into an array in the format
     * expected by the Elasticsearch API. This method should be called
     * last to get the final query.
     * 
     * @return array Array with the complete Elasticsearch query
     * 
     * @example
     * $queryArray = $query->match('title', 'PHP')
     *                     ->filter('status', 'published')
     *                     ->size(10)
     *                     ->build();
     * // Returns: ['query' => [...], 'size' => 10, ...]
     */
    public function build(): array;

    /**
     * Returns the query formatted as JSON for debugging
     * 
     * Converts the current query into a formatted, readable JSON string,
     * ideal for debugging, logging, or visualizing the generated DSL.
     * 
     * @param int $flags json_encode flags (default: JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
     * @return string Formatted Elasticsearch query JSON
     * 
     * @example
     * echo $query->match('title', 'PHP')
     *            ->filter('status', 'published')
     *            ->toDSL();
     * 
     * // Output:
     * // {
     * //     "query": {
     * //         "bool": {
     * //             "must": [
     * //                 {"match": {"title": "PHP"}}
     * //             ],
     * //             "filter": [
     * //                 {"term": {"status": "published"}}
     * //             ]
     * //         }
     * //     }
     * // }
     */
    public function toDSL(int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): string;

    /**
     * Eloquent-style WHERE conditions
     */

    /**
     * Adds a WHERE condition similar to Eloquent
     * 
     * @param string $field Field name
     * @param mixed $operator Operator or value if only 2 parameters
     * @param mixed $value Value (optional if using 2-parameter syntax)
     * @return self For method chaining
     */
    public function where(string $field, $operator, $value = null): self;

    /**
     * Adds a WHERE IN condition
     * 
     * @param string $field Field name
     * @param array $values Array of values
     * @return self For method chaining
     */
    public function whereIn(string $field, array $values): self;

    /**
     * Adds a WHERE NOT IN condition
     * 
     * @param string $field Field name
     * @param array $values Array of values to exclude
     * @return self For method chaining
     */
    public function whereNotIn(string $field, array $values): self;

    /**
     * Adds a WHERE BETWEEN condition
     * 
     * @param string $field Field name
     * @param array $values Array with [min, max] values
     * @return self For method chaining
     */
    public function whereBetween(string $field, array $values): self;

    /**
     * Adds a WHERE NOT NULL condition
     * 
     * @param string $field Field name
     * @return self For method chaining
     */
    public function whereNotNull(string $field): self;

    /**
     * Adds a WHERE NULL condition
     * 
     * @param string $field Field name
     * @return self For method chaining
     */
    public function whereNull(string $field): self;

    /**
     * Eloquent-style ordering and pagination
     */

    /**
     * Order results by field
     * 
     * @param string $column Field name
     * @param string $direction Sort direction (asc/desc)
     * @return self For method chaining
     */
    public function orderBy(string $column, string $direction = 'asc'): self;

    /**
     * Order results by field in descending order
     * 
     * @param string $column Field name
     * @return self For method chaining
     */
    public function orderByDesc(string $column): self;

    /**
     * Order by latest (descending)
     * 
     * @param string $column Field name (default: created_at)
     * @return self For method chaining
     */
    public function latest(string $column = 'created_at'): self;

    /**
     * Order by oldest (ascending)
     * 
     * @param string $column Field name (default: created_at)
     * @return self For method chaining
     */
    public function oldest(string $column = 'created_at'): self;

    /**
     * Paginate results
     * 
     * @param int $perPage Items per page
     * @param int $page Page number
     * @return self For method chaining
     */
    public function paginate(int $perPage = 15, int $page = 1): self;

    /**
     * Limit results (alias for size)
     * 
     * @param int $limit Maximum number of results
     * @return self For method chaining
     */
    public function limit(int $limit): self;

    /**
     * Text search methods
     */

    /**
     * Multi-field text search
     * 
     * @param string $query Search query
     * @param array $fields Fields to search (with optional boost)
     * @param float $boost Global boost for the query
     * @return self For method chaining
     * 
     * @example
     * $query->search('Laravel PHP', ['title^3', 'content', 'tags^2'])
     *        ->search('tutorial', ['title^2', 'description']);
     */
    public function search(string $query, array $fields = ['_all'], float $boost = 1.0): self;

    /**
     * Search in specific field
     * 
     * @param string $field Field name
     * @param string $query Search query
     * @return self For method chaining
     * 
     * @example
     * $query->searchIn('title', 'PHP tutorial')
     *        ->searchIn('content', 'Laravel framework');
     */
    public function searchIn(string $field, string $query): self;

    /**
     * Exact phrase search
     * 
     * @param string $field Field name
     * @param string $phrase Exact phrase to search
     * @return self For method chaining
     * 
     * @example
     * $query->searchPhrase('content', 'step by step tutorial')
     *        ->searchPhrase('title', 'complete guide');
     */
    public function searchPhrase(string $field, string $phrase): self;

    /**
     * Conditional methods
     */

    /**
     * Apply callback when condition is true
     * 
     * @param mixed $condition Condition to check
     * @param callable $callback Callback to apply
     * @param callable|null $default Default callback if condition is false
     * @return self For method chaining
     * 
     * @example
     * $query->when($userIsAdmin, function($q) {
     *     return $q->where('status', 'private');
     * })->when($featured, function($q) {
     *     return $q->where('featured', true);
     * });
     */
    public function when($condition, callable $callback, callable $default = null): self;

    /**
     * Apply callback when condition is false
     * 
     * @param mixed $condition Condition to check
     * @param callable $callback Callback to apply
     * @param callable|null $default Default callback if condition is true
     * @return self For method chaining
     * 
     * @example
     * $query->unless($userIsGuest, function($q) {
     *     return $q->whereNotNull('premium_features');
     * })->unless($isPublic, function($q) {
     *     return $q->where('visibility', 'private');
     * });
     */
    public function unless($condition, callable $callback, callable $default = null): self;

    /**
     * Predefined scopes
     */

    /**
     * Filter by active status
     * 
     * @return self For method chaining
     * 
     * @example
     * $query->active() // Filters for status = 'active'
     *        ->search('products');
     */
    public function active(): self;

    /**
     * Filter by published status
     * 
     * @return self For method chaining
     * 
     * @example
     * $query->published() // Filters for published content
     *        ->recent(30); // Published in last 30 days
     */
    public function published(): self;

    /**
     * Filter by recent records
     * 
     * @param int $days Number of days back
     * @return self For method chaining
     * 
     * @example
     * $query->recent(7)  // Last 7 days
     *        ->recent(30) // Last 30 days
     *        ->published();
     */
    public function recent(int $days = 30): self;

    /**
     * Utility methods
     */

    /**
     * Specify which fields to return
     * 
     * @param array|bool $fields Fields to include or false to exclude all
     * @return self For method chaining
     * 
     * @example
     * $query->source(['id', 'title', 'summary']) // Only these fields
     *        ->source(false); // No source fields (only metadata)
     */
    public function source(array|bool $fields): self;

    /**
     * Set query timeout
     * 
     * @param string $timeout Timeout value (e.g., '5s')
     * @return self For method chaining
     * 
     * @example
     * $query->timeout('5s')  // 5 seconds
     *        ->timeout('30s') // 30 seconds
     *        ->size(1000);
     */
    public function timeout(string $timeout): self;

    /**
     * Geographic search
     * 
     * @param string $field Field containing geo coordinates
     * @param array|string $location Location to search from
     * @param string $distance Maximum distance
     * @return self For method chaining
     * 
     * @example
     * $query->geoDistance('location', ['lat' => 40.7128, 'lon' => -74.0060], '5km')
     *        ->geoDistance('store_location', '40.7128,-74.0060', '10km');
     */
    public function geoDistance(string $field, array|string $location, string $distance): self;

    /**
     * Developer Experience methods
     */

    /**
     * Enable or disable verbose mode for detailed query building explanations
     * 
     * @param bool $enabled Whether to enable verbose mode
     * @return self For method chaining
     * 
     * @example
     * $query->verbose(true)
     *        ->search('Laravel tutorial')
     *        ->where('status', 'published');
     * $log = $query->getVerboseLog(); // See detailed build process
     */
    public function verbose(bool $enabled = true): self;
    
    /**
     * Get the verbose build log as an array of log messages
     * 
     * @return array Array of verbose log messages
     * 
     * @example
     * $query->verbose()->search('PHP')->where('status', 'active');
     * $log = $query->getVerboseLog();
     * // ['Added search for PHP', 'Added filter for status=active']
     */
    public function getVerboseLog(): array;
    
    /**
     * Get all warnings (general + performance warnings)
     * 
     * @return array Array of warning messages
     * 
     * @example
     * $query->size(15000)->from(50000); // Large pagination
     * $warnings = $query->getWarnings();
     * // ['Deep pagination detected', 'Large result set warning']
     */
    public function getWarnings(): array;
    
    /**
     * Get performance-specific warnings
     * 
     * @return array Array of performance warning messages
     * 
     * @example
     * $query->size(20000)->range('date', 'gte', '2020-01-01');
     * $perfWarnings = $query->getPerformanceWarnings();
     * // ['Large result set may impact performance']
     */
    public function getPerformanceWarnings(): array;
    
    /**
     * Translate Elasticsearch error messages to human-readable format
     * 
     * @param string $error The original error message
     * @return string Human-readable error message with actionable advice
     * 
     * @example
     * $readable = $query->translateError('parsing_exception');
     * // 'Query syntax error. Check field names and operators.'
     */
    public function translateError(string $error): string;
}
