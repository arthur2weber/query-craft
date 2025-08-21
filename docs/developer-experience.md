# 🔧 Developer Experience Guide

QueryCraft includes powerful debugging and optimization tools to improve your development workflow.

## 💎 For Laravel Developers

**Familiar debugging patterns for Eloquent users:**

```php
use Arthur2weber\QueryCraft\ElasticQuery;

// Laravel-style debugging
$query = new ElasticQuery();
$query->verbose(); // Like DB::enableQueryLog()

$result = $query
    ->where('status', 'published')           // Same as Eloquent
    ->whereIn('category', ['tech', 'news'])  // Same as Eloquent  
    ->orderByDesc('created_at')              // Same as Eloquent
    ->paginate(15)                           // Same as Eloquent
    ->build();

// Debug like Laravel
$logs = $query->getVerboseLog();             // Like DB::getQueryLog()
$warnings = $query->getWarnings();          // Performance insights
```

**Migration-friendly debugging:**
- Use `verbose()` just like `DB::enableQueryLog()`
- Check `getVerboseLog()` just like `DB::getQueryLog()`
- Performance warnings help optimize just like Laravel Debugbar
- Error translation makes debugging easier

## 🔍 Verbose Mode

Enable detailed logging to understand query building steps:

```php
use Arthur2weber\QueryCraft\ElasticQuery;

$query = new ElasticQuery();
$query->verbose(); // Enable verbose logging

$result = $query
    ->search('PHP Laravel', ['title^3', 'content'])
    ->filter('status', 'published')
    ->range('created_at', 'gte', '2024-01-01')
    ->size(10)
    ->build();

// Get detailed logs
$logs = $query->getVerboseLog();
foreach ($logs as $log) {
    echo $log . "\n";
}
```

**Example Output:**
```
[VERBOSE] Added multi_match query on fields 'title^3,content' for value 'PHP Laravel'
[VERBOSE] Added term filter on field 'status' for value 'published'
[VERBOSE] Added range filter on field 'created_at' with condition 'gte' and value '2024-01-01'
[VERBOSE] Set result size to 10
```

## ⚠️ Performance Warnings

Automatic detection of potentially slow queries:

```php
$query = new ElasticQuery();
$query->verbose();

$result = $query
    ->wildcard('title', '*slow*')     // Triggers warning
    ->size(50000)                     // Triggers warning
    ->from(10000)                     // Triggers warning
    ->build();

// Get performance warnings
$warnings = $query->getPerformanceWarnings();
foreach ($warnings as $warning) {
    echo "⚠️ " . $warning . "\n";
}
```

**Example Warnings:**
```
⚠️ Wildcard queries starting or ending with * can be slow on large datasets
⚠️ Large result sets (>10000) can impact performance
⚠️ Deep pagination (offset >5000) can be slow
```

## 🚨 Error Translation

Convert Elasticsearch errors to human-readable messages:

```php
$query = new ElasticQuery();

// Elasticsearch error example
$error = 'parsing_exception: [match] query does not support [invalid_parameter]';

// Translate to readable format
$readable = $query->translateError($error);
echo $readable;
// Output: "There's a syntax error in your query structure. Check the field names and query format."
```

## 🔧 Complete DX Example

Full debugging workflow:

```php
<?php
use Arthur2weber\QueryCraft\ElasticQuery;

// Enable debugging
$query = new ElasticQuery();
$query->verbose();

try {
    $result = $query
        ->search('PHP Tutorial', ['title^3', 'content'])
        ->wildcard('content', '*beginner*')  // Will warn about performance
        ->filter('status', 'published')
        ->size(5000)                         // Will warn about large result set
        ->build();
    
    // Display verbose logs
    echo "=== QUERY BUILDING LOGS ===\n";
    foreach ($query->getVerboseLog() as $log) {
        echo $log . "\n";
    }
    
    // Display warnings
    echo "\n=== WARNINGS ===\n";
    foreach ($query->getWarnings() as $warning) {
        echo "⚠️ " . $warning . "\n";
    }
    
    // Use with Elasticsearch
    $response = $client->search([
        'index' => 'articles',
        'body' => $result
    ]);
    
} catch (Exception $e) {
    echo "=== ERROR TRANSLATION ===\n";
    echo "Original: " . $e->getMessage() . "\n";
    echo "Translated: " . $query->translateError($e->getMessage()) . "\n";
}
```

## 📊 DX Methods Reference

| Method | Description | Returns |
|--------|-------------|---------|
| `verbose()` | Enable verbose logging | `ElasticQuery` |
| `getVerboseLog()` | Get array of verbose messages | `array` |
| `getWarnings()` | Get all warnings | `array` |
| `getPerformanceWarnings()` | Get performance warnings only | `array` |
| `translateError($error)` | Human-readable error translation | `string` |

## 💡 Best Practices

### 1. Enable Verbose Mode in Development
```php
// Always use in development
if (app()->environment('local')) {
    $query->verbose();
}
```

### 2. Monitor Performance Warnings
```php
$result = $query->/* your query */->build();

if ($warnings = $query->getPerformanceWarnings()) {
    logger()->warning('Query performance issues', $warnings);
}
```

### 3. Use Error Translation for User Feedback
```php
try {
    $response = $client->search(['index' => 'articles', 'body' => $result]);
} catch (Exception $e) {
    $userFriendlyMessage = $query->translateError($e->getMessage());
    return response()->json(['error' => $userFriendlyMessage], 400);
}
```

### 4. Debug Complex Queries Step by Step
```php
$query = new ElasticQuery();
$query->verbose();

// Build query step by step
$query->search($term, ['title^3', 'content']);
echo "After search: " . count($query->getVerboseLog()) . " steps\n";

$query->filter('status', 'published');
echo "After filter: " . count($query->getVerboseLog()) . " steps\n";

$result = $query->build();
```

## 🔍 Troubleshooting Common Issues

### Slow Queries
```php
// Check for performance warnings
$warnings = $query->getPerformanceWarnings();
if (in_array('Deep pagination', $warnings)) {
    // Use search_after instead of from/size for deep pagination
}
```

### Unexpected Results
```php
// Use verbose mode to understand query building
$query->verbose();
$result = $query->build();

// Check the actual generated query
echo json_encode($result, JSON_PRETTY_PRINT);
```

### Error Debugging
```php
try {
    $response = $client->search(['index' => 'articles', 'body' => $result]);
} catch (Exception $e) {
    // Get both technical and user-friendly error info
    echo "Technical: " . $e->getMessage() . "\n";
    echo "User-friendly: " . $query->translateError($e->getMessage()) . "\n";
    
    // Check query building steps
    foreach ($query->getVerboseLog() as $log) {
        echo $log . "\n";
    }
}
```

---

**💡 Pro Tip**: Always enable verbose mode during development. It helps you understand exactly what QueryCraft is doing and catch potential issues early.

```php
$query = new ElasticQuery();
$result = $query
    ->verbose()  // Enable verbose mode
    ->match('content', 'Laravel tutorial')
    ->should('boost', 'rating', 'gte', 4)
    ->filter('category', 'programming')
    ->sort('created_at', 'desc')
    ->size(20)
    ->build();

// All steps above are logged in verbose mode
$logs = $query->getVerboseLog();
```

## ⚠️ Performance Warnings

QueryCraft automatically detects potentially slow query patterns and provides actionable warnings.

### Automatic Warning Detection

```php
$query = new ElasticQuery();
$query->verbose(); // Enable to see warnings

// These operations will trigger performance warnings
$query
    ->wildcard('title', '*slow*')           // Wildcard at start/end
    ->size(50000)                          // Large result set
    ->from(10000)                          // Deep pagination
    ->regexp('description', '.*complex.*'); // Regex query

// Get performance-specific warnings
$performanceWarnings = $query->getPerformanceWarnings();
foreach ($performanceWarnings as $warning) {
    echo "⚠️ PERFORMANCE: " . $warning . "\n";
}

// Get all warnings (general + performance)
$allWarnings = $query->getWarnings();
foreach ($allWarnings as $warning) {
    echo "🔔 " . $warning . "\n";
}
```

### Warning Types

| Warning Type | Trigger | Recommendation |
|-------------|---------|----------------|
| **Wildcard** | `*` at start/end | Use filters or match queries instead |
| **Large Results** | `size > 10000` | Use pagination or scroll API |
| **Deep Pagination** | `from > 5000` | Use search_after for deep pagination |
| **Regex Queries** | `regexp()` method | Use wildcard or match for better performance |

### Example Warning Output

```
⚠️ Wildcard queries starting or ending with * can be slow on large datasets
⚠️ Large result sets (>10000) can impact performance
⚠️ Deep pagination (offset >5000) can be slow - consider using search_after
⚠️ Regular expression queries can be slow on large text fields
```

## 🚨 Smart Error Translation

Convert cryptic Elasticsearch errors into human-readable messages with actionable advice.

### Basic Error Translation

```php
$query = new ElasticQuery();

// Example Elasticsearch errors
$errors = [
    'parsing_exception: [match] query does not support [invalid_parameter]',
    'search_phase_execution_exception timeout',
    'index_not_found_exception: no such index [missing_index]',
    'mapper_parsing_exception: failed to parse field [date_field]'
];

foreach ($errors as $error) {
    $humanReadable = $query->translateError($error);
    echo "Original: " . $error . "\n";
    echo "Translated: " . $humanReadable . "\n\n";
}
```

### Error Translation Examples

```
Original: "parsing_exception: [match] query does not support [invalid_parameter]"
Translated: "There's a syntax error in your query structure. Check the field names and query format according to Elasticsearch documentation."

Original: "search_phase_execution_exception timeout"
Translated: "Search operation timed out. Try simplifying your query, adding filters to reduce the dataset, or increase the timeout setting."

Original: "index_not_found_exception: no such index [products]"
Translated: "The index 'products' doesn't exist. Make sure the index name is correct and the index has been created."
```

### Supported Error Patterns

- **Parsing Errors**: Query syntax and structure issues
- **Timeout Errors**: Search phase and request timeouts
- **Index Errors**: Missing or invalid indexes
- **Field Errors**: Mapping and field-related issues
- **Authentication**: Security and permission errors
- **Resource Errors**: Memory and capacity issues

## 🎯 Best Practices

### 1. Always Use Verbose Mode During Development

```php
// Development
$query = new ElasticQuery();
$query->verbose(); // Always enable in development

// Production
$query = new ElasticQuery();
// Don't enable verbose in production for performance
```

### 2. Check Performance Warnings Regularly

```php
$query = new ElasticQuery();
$query->verbose();

// Build your query
$result = $query->buildComplexQuery();

// Always check for warnings
$warnings = $query->getPerformanceWarnings();
if (!empty($warnings)) {
    foreach ($warnings as $warning) {
        error_log("Query Performance Warning: " . $warning);
    }
}
```

### 3. Use Error Translation in Exception Handling

```php
try {
    $results = $elasticsearchClient->search($query->build());
} catch (Exception $e) {
    $humanError = $query->translateError($e->getMessage());
    
    // Log both original and translated errors
    error_log("Elasticsearch Error: " . $e->getMessage());
    error_log("Human Readable: " . $humanError);
    
    // Show user-friendly message
    throw new Exception($humanError);
}
```

### 4. Complete DX Workflow Example

```php
<?php
use Arthur2weber\QueryCraft\ElasticQuery;

function searchWithDX($searchTerm, $filters = []) {
    $query = new ElasticQuery();
    $query->verbose(); // Enable DX features
    
    try {
        // Build query
        $result = $query
            ->match('title', $searchTerm)
            ->filter('status', 'published');
            
        // Apply additional filters
        foreach ($filters as $field => $value) {
            $result->filter($field, $value);
        }
        
        $queryArray = $result->build();
        
        // Log verbose information in development
        if (APP_ENV === 'development') {
            error_log("=== QUERY BUILDING LOGS ===");
            foreach ($query->getVerboseLog() as $log) {
                error_log($log);
            }
            
            // Check for performance warnings
            $warnings = $query->getPerformanceWarnings();
            if (!empty($warnings)) {
                error_log("=== PERFORMANCE WARNINGS ===");
                foreach ($warnings as $warning) {
                    error_log("⚠️ " . $warning);
                }
            }
        }
        
        return $queryArray;
        
    } catch (Exception $e) {
        // Translate error for better debugging
        $humanError = $query->translateError($e->getMessage());
        error_log("Query Error: " . $humanError);
        throw new Exception($humanError);
    }
}
```

## 🔧 Troubleshooting

### Common Issues and Solutions

#### Issue: No verbose logs appearing
```php
// ❌ Wrong - verbose mode not enabled
$query = new ElasticQuery();
$result = $query->match('title', 'test')->build();
$logs = $query->getVerboseLog(); // Will be empty

// ✅ Correct - enable verbose mode first
$query = new ElasticQuery();
$query->verbose(); // Enable verbose mode
$result = $query->match('title', 'test')->build();
$logs = $query->getVerboseLog(); // Will contain logs
```

#### Issue: Missing performance warnings
```php
// ❌ Wrong - not checking the right method
$warnings = $query->getVerboseLog(); // This is not warnings

// ✅ Correct - use the right methods
$performanceWarnings = $query->getPerformanceWarnings();
$allWarnings = $query->getWarnings();
```

#### Issue: Error translation not working
```php
// ❌ Wrong - passing wrong error format
$translated = $query->translateError("Generic error message");

// ✅ Correct - pass actual Elasticsearch error
$elasticsearchError = 'parsing_exception: [match] query does not support [invalid_param]';
$translated = $query->translateError($elasticsearchError);
```

### Debugging Tips

1. **Always enable verbose mode during development**
2. **Check warnings after building complex queries**
3. **Use error translation in try-catch blocks**
4. **Log both original and translated errors**
5. **Monitor performance warnings in staging environments**

## 📚 API Reference

| Method | Description | Returns |
|--------|-------------|---------|
| `verbose()` | Enable verbose logging mode | `ElasticQuery` |
| `getVerboseLog()` | Get array of verbose log messages | `array<string>` |
| `getWarnings()` | Get all warnings (general + performance) | `array<string>` |
| `getPerformanceWarnings()` | Get performance-specific warnings | `array<string>` |
| `translateError($error)` | Translate error to human-readable format | `string` |

---

*For more examples and advanced usage, check the [examples directory](../examples/) and [test cases](../tests/Unit/Core/DeveloperExperienceTest.php).*
