<?php

namespace Arthur2weber\QueryCraft\Query;

/**
 * BaseQuery - Universal Query Builder Foundation
 * 
 * Abstract base class that provides the common fluent interface for all query builders.
 * This class implements the core query building methods that are common across different
 * database systems (Elasticsearch, MongoDB, GraphQL, etc.).
 * 
 * @package Arthur2weber\QueryCraft\Query
 * @author Arthur Weber <arthur2weber@gmail.com>
 * @version 1.0.0
 */
abstract class BaseQuery
{
    protected array $wheres = [];
    protected array $sorts = [];
    protected ?int $limitValue = null;
    protected ?int $offsetValue = null;
    protected string $from = '';
    protected array $selects = ['*'];
    
    // Developer Experience Enhancement Properties
    protected bool $verboseMode = false;
    protected array $buildLog = [];
    protected array $warnings = [];
    protected array $performanceWarnings = [];

    /**
     * Set the collection/index/table to query from OR set the offset for backends that use numeric "from".
     * Accepts either a string (collection/index name) or an int (offset).
     */
    public function from(string|int $collection): static
    {
        if (is_int($collection)) {
            // numeric 'from' used by backends like Elasticsearch
            $this->offsetValue = $collection;
            $this->logOperation('from', ['offset' => $collection]);
            return $this;
        }

        $this->from = (string) $collection;
        $this->logOperation('from', ['collection' => (string) $collection]);
        return $this;
    }

    /**
     * Add a basic where clause to the query
     */
    public function where(string $field, mixed $operator, mixed $value = null): static
    {
        // If only two args were passed, decide whether the second arg is an operator
        // (e.g. 'null', 'not_null') or a value. Previously we always treated it as
        // a value when $value === null which made calls like where('j', 'null')
        // behave incorrectly. Now we preserve it as an operator for a small set of
        // operator-only tokens.
        if ($value === null) {
            $operatorLower = is_string($operator) ? strtolower($operator) : null;
            $operatorOnlyTokens = ['null', 'not_null'];
            if (!in_array($operatorLower, $operatorOnlyTokens, true)) {
                $value = $operator;
                $operator = '=';
            }
        }

        $this->validateFieldName($field);
        $this->validateValue($value);

        $this->wheres[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'type' => 'where',
            'boolean' => 'and'
        ];

        $this->logOperation('where', [
            'field' => $field,
            'operator' => $operator,
            'value' => $value
        ]);

        return $this;
    }

    /**
     * Add a where in clause to the query
     */
    public function whereIn(string $field, array $values): static
    {
        $this->validateFieldName($field);
        $this->validateNonEmptyArray($values, 'WhereIn values');
        
        foreach ($values as $value) {
            $this->validateValue($value);
        }

        $this->wheres[] = [
            'field' => $field,
            'operator' => 'in',
            'value' => $values,
            'type' => 'whereIn',
            'boolean' => 'and'
        ];

        $this->logOperation('whereIn', [
            'field' => $field,
            'values' => $values
        ]);

        return $this;
    }

    /**
     * Add a where not in clause to the query
     */
    public function whereNotIn(string $field, array $values): static
    {
        $this->validateFieldName($field);
        $this->validateNonEmptyArray($values, 'WhereNotIn values');
        
        foreach ($values as $value) {
            $this->validateValue($value);
        }

        $this->wheres[] = [
            'field' => $field,
            'operator' => 'not_in',
            'value' => $values,
            'type' => 'whereNotIn',
            'boolean' => 'and'
        ];

        $this->logOperation('whereNotIn', [
            'field' => $field,
            'values' => $values
        ]);

        return $this;
    }

    /**
     * Add a where between clause to the query
     */
    public function whereBetween(string $field, array $range): static
    {
        $this->validateFieldName($field);
        
        if (count($range) !== 2) {
            throw new \InvalidArgumentException('Range must contain exactly two values');
        }

        foreach ($range as $value) {
            $this->validateValue($value);
        }

        $this->wheres[] = [
            'field' => $field,
            'operator' => 'between',
            'value' => $range,
            'type' => 'whereBetween',
            'boolean' => 'and'
        ];

        $this->logOperation('whereBetween', [
            'field' => $field,
            'range' => $range
        ]);

        return $this;
    }

    /**
     * Add a where not between clause to the query
     */
    public function whereNotBetween(string $field, array $range): static
    {
        $this->validateFieldName($field);
        
        if (count($range) !== 2) {
            throw new \InvalidArgumentException('Range must contain exactly two values');
        }

        foreach ($range as $value) {
            $this->validateValue($value);
        }

        $this->wheres[] = [
            'field' => $field,
            'operator' => 'not_between',
            'value' => $range,
            'type' => 'whereNotBetween',
            'boolean' => 'and'
        ];

        $this->logOperation('whereNotBetween', [
            'field' => $field,
            'range' => $range
        ]);

        return $this;
    }

    /**
     * Add a where null clause to the query
     */
    public function whereNull(string $field): static
    {
        $this->validateFieldName($field);

        $this->wheres[] = [
            'field' => $field,
            'operator' => 'null',
            'value' => null,
            'type' => 'whereNull',
            'boolean' => 'and'
        ];

        $this->logOperation('whereNull', ['field' => $field]);

        return $this;
    }

    /**
     * Add a where not null clause to the query
     */
    public function whereNotNull(string $field): static
    {
        $this->validateFieldName($field);

        $this->wheres[] = [
            'field' => $field,
            'operator' => 'not_null',
            'value' => null,
            'type' => 'whereNotNull',
            'boolean' => 'and'
        ];

        $this->logOperation('whereNotNull', ['field' => $field]);

        return $this;
    }

    /**
     * Add an OR where clause to the query
     */
    public function orWhere(string $field, mixed $operator, mixed $value = null): static
    {
        // Same behavior as where(): allow operator-only tokens to be passed as the
        // second argument without being interpreted as the value.
        if ($value === null) {
            $operatorLower = is_string($operator) ? strtolower($operator) : null;
            $operatorOnlyTokens = ['null', 'not_null'];
            if (!in_array($operatorLower, $operatorOnlyTokens, true)) {
                $value = $operator;
                $operator = '=';
            }
        }

        $this->validateFieldName($field);
        $this->validateValue($value);

        $this->wheres[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'type' => 'where',
            'boolean' => 'or'
        ];

        $this->logOperation('orWhere', [
            'field' => $field,
            'operator' => $operator,
            'value' => $value
        ]);

        return $this;
    }

    /**
     * Add an order by clause to the query
     */
    public function orderBy(string $field, string $direction = 'asc'): static
    {
        $this->validateFieldName($field);
        
        $direction = strtolower($direction);
        if (!in_array($direction, ['asc', 'desc'])) {
            throw new \InvalidArgumentException("Invalid sort direction: $direction. Must be 'asc' or 'desc'");
        }

        $this->sorts[] = [
            'field' => $field,
            'direction' => $direction
        ];

        $this->logOperation('orderBy', [
            'field' => $field,
            'direction' => $direction
        ]);

        return $this;
    }

    /**
     * Add an order by descending clause to the query
     */
    public function orderByDesc(string $field): static
    {
        return $this->orderBy($field, 'desc');
    }

    /**
     * Set the maximum number of records to return
     */
    public function take(int $limit): static
    {
        if ($limit < 0) {
            throw new \InvalidArgumentException('Limit must be a non-negative integer');
        }

        $this->limitValue = $limit;
        $this->logOperation('take', ['limit' => $limit]);

        return $this;
    }

    /**
     * Set the number of records to skip
     */
    public function skip(int $offset): static
    {
        if ($offset < 0) {
            throw new \InvalidArgumentException('Offset must be a non-negative integer');
        }

        $this->offsetValue = $offset;
        $this->logOperation('skip', ['offset' => $offset]);

        return $this;
    }

    /**
     * Alias for take()
     */
    public function limit(int $limit): static
    {
        return $this->take($limit);
    }

    /**
     * Alias for skip()
     */
    public function offset(int $offset): static
    {
        return $this->skip($offset);
    }

    /**
     * Set the fields to select
     */
    public function select(array $fields): static
    {
        $this->validateNonEmptyArray($fields, 'Select fields');
        
        foreach ($fields as $field) {
            if (!is_string($field)) {
                throw new \InvalidArgumentException('Select fields must be strings');
            }
            $this->validateFieldName($field);
        }

        $this->selects = $fields;
        $this->logOperation('select', ['fields' => $fields]);

        return $this;
    }

    /**
     * Enable verbose mode for debugging
     */
    public function verbose(bool $enabled = true): static
    {
        $this->verboseMode = $enabled;
        return $this;
    }

    /**
     * Get the build log for debugging
     */
    public function getBuildLog(): array
    {
        return $this->buildLog;
    }

    /**
     * Get any warnings generated during query building
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Get performance warnings
     */
    public function getPerformanceWarnings(): array
    {
        return $this->performanceWarnings;
    }

    /**
     * Log an operation for debugging purposes
     */
    protected function logOperation(string $operation, array $parameters = []): void
    {
        if ($this->verboseMode) {
            $this->buildLog[] = [
                'operation' => $operation,
                'parameters' => $parameters,
                'timestamp' => microtime(true)
            ];
        }
    }

    /**
     * Add a warning message
     */
    protected function addWarning(string $message): void
    {
        $this->warnings[] = $message;
    }

    /**
     * Add a performance warning
     */
    protected function addPerformanceWarning(string $message): void
    {
        $this->performanceWarnings[] = $message;
    }

    /**
     * Validates field names to ensure they are valid
     */
    protected function validateFieldName(string $field): void
    {
        if ($field === '' || ctype_space($field)) {
            throw new \InvalidArgumentException('Field name cannot be empty');
        }
        
        if (strpos($field, ' ') !== false) {
            $this->addWarning("Field name '$field' contains spaces. This might cause issues depending on the backend configuration.");
        }
    }

    /**
     * Validates values to ensure they can be safely processed
     */
    protected function validateValue($value): void
    {
        if (is_resource($value) || is_callable($value)) {
            throw new \InvalidArgumentException('Unsupported data type: ' . gettype($value) . '. Resources and callables cannot be processed.');
        }
        
        if (is_numeric($value) && !is_finite($value)) {
            throw new \InvalidArgumentException('Infinite and NaN values are not supported');
        }
        
        if (is_string($value) && function_exists('mb_check_encoding') && !\mb_check_encoding($value, 'UTF-8')) {
            throw new \InvalidArgumentException('Invalid UTF-8 encoding detected in string value');
        }
        
        if (is_string($value) && preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
            throw new \InvalidArgumentException('String contains control characters that may cause processing issues');
        }
        
        if (is_string($value) && strlen($value) > 32768) {
            $this->addPerformanceWarning('String value is very long (' . strlen($value) . ' bytes). This may cause performance issues.');
        }
    }

    /**
     * Validates arrays to ensure they are not empty when required
     */
    protected function validateNonEmptyArray(array $array, string $context = 'Array'): void
    {
        if (empty($array)) {
            throw new \InvalidArgumentException("$context cannot be empty");
        }
    }

    /**
     * Abstract methods that each implementation must provide.
     * Signatures are kept intentionally permissive to allow backend-specific
     * implementations while providing a common contract for consumers.
     */
    abstract public function get(): array;
    abstract public function first(): mixed;
    abstract public function count(): int;
    abstract public function paginate(int $perPage = 15, int $page = 1): array;
    abstract public function toQuery(): mixed;
}
