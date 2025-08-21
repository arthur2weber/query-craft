# QueryCraft - Quick Reference Guide

> **Complete method reference for QueryCraft Elasticsearch DSL Query Builder**
>
> Perfect for GitHub Copilot integration and daily development.

## 💎 For Laravel Developers

**Zero learning curve! All your Eloquent methods work exactly the same:**

```php
// Your familiar Eloquent syntax
$users = User::where('status', 'active')
            ->whereIn('role', ['admin', 'editor'])
            ->orderByDesc('created_at')
            ->paginate(15);

// Identical QueryCraft syntax
$docs = (new ElasticQuery())
       ->where('status', 'active')
       ->whereIn('role', ['admin', 'editor'])
       ->orderByDesc('created_at')
       ->paginate(15);
```

**All Eloquent methods supported:**
- `where()`, `whereIn()`, `whereNotIn()`, `whereBetween()`, `whereNull()`, `whereNotNull()`
- `orderBy()`, `orderByDesc()`, `latest()`, `oldest()`
- `paginate()`, `limit()`, `offset()`, `take()`, `skip()`
- `when()`, `unless()`, `count()`, `avg()`, `sum()`, `max()`, `min()`
- Plus powerful Elasticsearch features like `search()`, `geoDistance()`, aggregations

## 🚀 Installation

```bash
composer require arthur2weber/query-craft
```

## 📚 Basic Usage

```php
<?php
use Arthur2weber\QueryCraft\ElasticQuery;

// Simple search with filters
$query = new ElasticQuery();
$result = $query
    ->search('search term', ['title^3', 'content'])
    ->filter('status', 'published')
    ->range('created_at', 'gte', '2024-01-01')
    ->sort('_score', 'desc')
    ->size(10)
    ->build();

// Use with Elasticsearch PHP Client
$response = $client->search([
    'index' => 'my_index',
    'body' => $result
]);
```

## 🔍 Query Methods

### 💎 Eloquent-Style Methods (Laravel Developers)

```php
// All your familiar where clauses
->where('status', 'published')         // Exact match
->where('price', '>', 100)             // Comparison operators
->whereIn('category', ['tech', 'news']) // Array matching
->whereNotIn('status', ['draft'])      // Exclude values  
->whereBetween('price', [10, 100])     // Range
->whereNotNull('published_at')         // Field exists
->whereNull('deleted_at')              // Field missing

// Familiar sorting
->orderBy('created_at', 'desc')        // Basic sorting
->orderByDesc('views')                 // Descending shortcut
->latest('published_at')               // Most recent first
->oldest('created_at')                 // Oldest first

// Laravel-style pagination
->paginate(20, 3)                      // 20 per page, page 3
->limit(10)                            // Alias for size(10)
->offset(20)                           // Alias for from(20)
->take(15)                             // Alias for size(15)
->skip(30)                             // Alias for from(30)

// Conditional queries
->when($condition, function($q) {
    return $q->where('featured', true);
})
->unless($isAdmin, function($q) {
    return $q->where('public', true);
})

// Aggregations
->count()                              // Total count
->avg('rating')                        // Average
->sum('amount')                        // Sum
->max('price')                         // Maximum
->min('price')                         // Minimum

// Common scopes
->active()                             // where('status', 'active')
->published()                          // where('status', 'published')
->recent(30)                           // Last 30 days
```

### Text Queries
```php
// Basic text search
->match('field', 'value')
->search('value', ['field1', 'field2'])
->matchPhrase('field', 'exact phrase')
->queryString('field:value AND other:term')

// With boost
->matchBoost('field', 'value', 2.0)
->termBoost('field', 'value', 1.5)
```

### Term Queries
```php
->term('field', 'exact_value')
->terms('field', ['value1', 'value2'])
->range('field', 'gte', 100)
->exists('field')
->prefix('field', 'pre')
->wildcard('field', 'val*')
```

### Boolean Logic
```php
// Must (AND)
->must(ElasticQuery::matchClause('field', 'value'))

// Should (OR)  
->should(ElasticQuery::termClause('field', 'value'))

// Must Not (NOT)
->mustNot(ElasticQuery::rangeClause('field', 'lt', 10))

// Filter (efficient AND)
->filter('field', 'value')
```

### Fuzzy & Advanced
```php
->fuzzy('field', 'value', 2)           // 2 char difference
->regexp('field', '[a-z]+@[a-z]+')     // regex pattern
->nested('path', $nestedQuery)         // nested objects
```

### Geographic Queries
```php
->geoDistance('location', '40.7,-74.0', '10km')
->geoBoundingBox('location', [
    'top_left' => '40.8,-74.1',
    'bottom_right' => '40.6,-73.9'
])
```

## 📊 Aggregations

### Basic Aggregations
```php
// Terms aggregation (categories)
->aggregation('categories', [
    'terms' => ['field' => 'category.keyword']
])

// Date histogram (time series)
->aggregation('monthly', [
    'date_histogram' => [
        'field' => 'date',
        'calendar_interval' => 'month'
    ]
])

// Statistics
->aggregation('price_stats', [
    'stats' => ['field' => 'price']
])
```

### Nested Aggregations
```php
// With nested sub-aggregations
->aggregation('categories', [
    'terms' => [
        'field' => 'category.keyword',
        'aggs' => [
            'avg_price' => [
                'avg' => ['field' => 'price']
            ]
        ]
    ]
])
```

## 📄 Pagination & Sorting

```php
// Pagination
->size(20)                    // Results per page
->from(40)                    // Offset (page 3)

// Sorting
->sort('date', 'desc')        // Single field
->sort([                      // Multiple fields
    'relevance' => 'desc',
    'date' => 'asc'
])

// Geographic sorting
->sort([
    '_geo_distance' => [
        'location' => '40.7,-74.0',
        'order' => 'asc'
    ]
])
```

## 🔧 Utilities

```php
// Source field filtering
->source(['id', 'title', 'summary'])

// Highlighting
->highlight(['title', 'content'])

// Performance
->timeout('5s')
->terminateAfter(10000)

// Minimum should match
->minimumShouldMatch(2)
->minimumShouldMatch('75%')
```

## 🔧 Developer Experience Methods

### Debugging & Verbose Mode
```php
// Enable verbose logging
->verbose()

// Get verbose logs
$logs = $query->getVerboseLog();

// Get performance warnings
$warnings = $query->getPerformanceWarnings();

// Get all warnings
$allWarnings = $query->getWarnings();

// Translate error messages
$humanError = $query->translateError($elasticsearchError);
```

### Example with DX Features
```php
$query = new ElasticQuery();
$query->verbose(); // Enable debugging

$result = $query
    ->match('title', 'PHP')
    ->filter('status', 'published')
    ->size(10)
    ->build();

// Check what happened
foreach ($query->getVerboseLog() as $log) {
    echo $log . "\n";
}

// Check for warnings
foreach ($query->getWarnings() as $warning) {
    echo "⚠️ " . $warning . "\n";
}
```

## 🎯 Common Patterns

### Search with Facets
```php
$query = new ElasticQuery();
$result = $query
    ->search($searchTerm, ['title^3', 'content'])
    ->filter('status', 'published')
    ->aggregation('categories', [
        'terms' => ['field' => 'category.keyword', 'size' => 10]
    ])
    ->aggregation('date_ranges', [
        'date_range' => [
            'field' => 'published_at',
            'ranges' => [
                ['from' => 'now-1M', 'to' => 'now'],
                ['from' => 'now-1y', 'to' => 'now-1M']
            ]
        ]
    ])
    ->sort('_score', 'desc')
    ->size(20)
    ->build();
```

### E-commerce Product Search
```php
$query = new ElasticQuery();
$result = $query
    ->search($productName, ['name^3', 'description', 'brand^2'])
    ->filter('in_stock', true)
    ->range('price', 'gte', $minPrice)
    ->range('price', 'lte', $maxPrice)
    ->range('rating', 'gte', 4.0)
    ->aggregation('brands', [
        'terms' => ['field' => 'brand.keyword']
    ])
    ->aggregation('price_ranges', [
        'range' => [
            'field' => 'price',
            'ranges' => [
                ['to' => 50],
                ['from' => 50, 'to' => 100],
                ['from' => 100, 'to' => 200],
                ['from' => 200]
            ]
        ]
    ])
    ->sort([
        '_score' => 'desc',
        'price' => 'asc'
    ])
    ->build();
```

### Geographic Search
```php
$query = new ElasticQuery();
$result = $query
    ->match('category', 'restaurant')
    ->geoDistance('location', $userLocation, '5km')
    ->filter('open_now', true)
    ->range('rating', 'gte', 3.5)
    ->aggregation('cuisines', [
        'terms' => ['field' => 'cuisine.keyword']
    ])
    ->sort([
        '_geo_distance' => [
            'location' => $userLocation,
            'order' => 'asc'
        ]
    ])
    ->build();
```

### Analytics Query
```php
$query = new ElasticQuery();
$result = $query
    ->range('timestamp', 'gte', 'now-30d')
    ->aggregation('daily_metrics', [
        'date_histogram' => [
            'field' => 'timestamp',
            'calendar_interval' => 'day',
            'aggs' => [
                'unique_users' => [
                    'cardinality' => ['field' => 'user_id']
                ],
                'total_revenue' => [
                    'sum' => ['field' => 'amount']
                ]
            ]
        ]
    ])
    ->size(0) // Only aggregations
    ->build();
```

## 🚨 Error Handling

```php
try {
    $query = new ElasticQuery();
    $result = $query
        ->match('title', $searchTerm)
        ->filter('status', 'published')
        ->build();
        
    // Use with Elasticsearch client
    $response = $client->search([
        'index' => 'my_index',
        'body' => $result
    ]);
} catch (Exception $e) {
    // Handle errors
    echo "Query error: " . $e->getMessage();
}
```

## 💡 Best Practices

1. **Use filters for exact matches** - More efficient than queries
2. **Filter before querying** - Apply filters first for better performance
3. **Limit source fields** - Use `source()` to reduce response size
4. **Set reasonable timeouts** - Prevent long-running queries
5. **Use aggregations wisely** - Set appropriate sizes for terms aggregations
6. **Boost strategically** - Higher boost for more important fields
7. **Test with real data** - Always test queries with your actual data structure

## 📖 More Resources

- **[README.md](../README.md)** - Complete documentation
- **[Examples](../examples/)** - Practical use cases
- **[Roadmap](ROADMAP.md)** - Future features
- **[Contributing](CONTRIBUTING.md)** - How to contribute

## 🚀 Laravel Integration

**Native Laravel integration with Service Provider, Facade, and auto-discovery.**

```bash
composer require arthur2weber/query-craft  # Auto-registers in Laravel 5.5+
```

**Clean facade syntax:**
```php
QueryCraft::search('Laravel tutorial')->where('status', 'published')->build()
```

**Controller injection:**
```php
public function search(Request $request, ElasticQuery $query) {
    return $query->search($request->q)->published()->build();
}
```

📖 **[Laravel Integration Guide](LARAVEL.md)**
