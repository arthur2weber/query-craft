# 🤖 GitHub Copilot Integration Guide

QueryCraft is designed to work seamlessly with GitHub Copilot for maximum productivity.

## 💎 For Laravel Developers

**GitHub Copilot recognizes your Eloquent patterns and suggests QueryCraft equivalents!**

When you type familiar Laravel patterns, Copilot will suggest QueryCraft methods:

```php
// Type: "User::where" → Copilot suggests:
$users = (new ElasticQuery())
    ->where('status', 'active')
    ->whereIn('role', ['admin', 'editor'])
    ->orderByDesc('created_at')
    ->paginate(15);

// Type: "$posts = Post::published()" → Copilot suggests:
$posts = (new ElasticQuery())
    ->published()
    ->recent(30)
    ->latest()
    ->limit(10);
```

**Your Eloquent muscle memory becomes QueryCraft productivity!**

## 💡 Copilot-Friendly Patterns

### Auto-completion Triggers

GitHub Copilot will suggest QueryCraft methods when you start typing these patterns:

```php
// Trigger: "$query = new ElasticQuery();"
$query = new ElasticQuery();
$result = $query
    // Copilot will suggest common methods like ->search(), ->filter(), ->sort()
```

### Common Patterns Copilot Recognizes

#### 1. E-commerce Search
```php
// Copilot trigger: "// Product search with filters"
$query = new ElasticQuery();
$result = $query
    ->search($searchTerm, ['name^3', 'description', 'brand^2'])
    ->filter('status', 'active')
    ->filter('in_stock', true)
    ->range('price', 'gte', $minPrice)
    ->range('price', 'lte', $maxPrice)
    ->aggregation('brands', [
        'terms' => ['field' => 'brand.keyword', 'size' => 10]
    ])
    ->aggregation('categories', [
        'terms' => ['field' => 'category.keyword', 'size' => 5]
    ])
    ->sort('_score', 'desc')
    ->size(20)
    ->build();
```

#### 2. Blog/Content Search
```php
// Copilot trigger: "// Blog post search"
$query = new ElasticQuery();
$result = $query
    ->search($searchTerm, ['title^3', 'content', 'excerpt^2'])
    ->filter('status', 'published')
    ->filter('featured', true)
    ->range('published_at', 'lte', 'now')
    ->aggregation('categories', [
        'terms' => ['field' => 'category.keyword']
    ])
    ->aggregation('tags', [
        'terms' => ['field' => 'tags.keyword', 'size' => 20]
    ])
    ->sort('published_at', 'desc')
    ->size(10)
    ->build();
```

#### 3. Geographic Search
```php
// Copilot trigger: "// Find nearby restaurants"
$query = new ElasticQuery();
$result = $query
    ->match('type', 'restaurant')
    ->geoDistance('location', $userLocation, '5km')
    ->filter('open_now', true)
    ->range('rating', 'gte', 4.0)
    ->aggregation('cuisines', [
        'terms' => ['field' => 'cuisine.keyword']
    ])
    ->sort([
        '_geo_distance' => [
            'location' => $userLocation,
            'order' => 'asc'
        ]
    ])
    ->size(20)
    ->build();
```

#### 4. Analytics & Aggregations
```php
// Copilot trigger: "// Analytics dashboard query"
$query = new ElasticQuery();
$result = $query
    ->range('timestamp', 'gte', 'now-30d')
    ->aggregation('daily_stats', [
        'date_histogram' => [
            'field' => 'timestamp',
            'calendar_interval' => 'day',
            'aggs' => [
                'unique_users' => [
                    'cardinality' => ['field' => 'user_id']
                ],
                'total_revenue' => [
                    'sum' => ['field' => 'amount']
                ],
                'avg_session_duration' => [
                    'avg' => ['field' => 'session_duration']
                ]
            ]
        ]
    ])
    ->size(0) // Only aggregations
    ->build();
```

## 🎯 Method Categories for Copilot

### Search Methods
```php
// Text search
->search($term, $fields)
->match($field, $value)
->matchPhrase($field, $phrase)
->queryString($query)

// Advanced search
->fuzzy($field, $value, $fuzziness)
->wildcard($field, $pattern)
->regexp($field, $pattern)
```

### Filter Methods
```php
// Basic filters
->filter($field, $value)
->terms($field, $values)
->range($field, $operator, $value)
->exists($field)

// Geographic filters
->geoDistance($field, $location, $distance)
->geoBoundingBox($field, $bounds)
```

### Boolean Logic
```php
// Boolean queries
->must($query)
->should($query)
->mustNot($query)
->filter($field, $value)
```

### Aggregations
```php
// Common aggregations
->aggregation($name, $config)

// Typical configs Copilot knows:
// Terms: ['terms' => ['field' => 'category.keyword']]
// Date histogram: ['date_histogram' => ['field' => 'date', 'calendar_interval' => 'month']]
// Stats: ['stats' => ['field' => 'price']]
```

### Utilities
```php
// Result formatting
->sort($field, $direction)
->size($count)
->from($offset)
->source($fields)
->highlight($fields)

// Developer experience
->verbose()
->getVerboseLog()
->getWarnings()
```

## 🔥 Copilot Best Practices

### 1. Use Descriptive Comments
```php
// E-commerce product search with price filters and brand facets
$query = new ElasticQuery();
// Copilot will suggest appropriate e-commerce methods
```

### 2. Chain Methods Predictably
```php
$query = new ElasticQuery();
$result = $query
    ->search(/* Copilot suggests search parameters */)
    ->filter(/* Copilot suggests common filters */)
    ->range(/* Copilot suggests range operators */)
    ->aggregation(/* Copilot suggests aggregation configs */)
    ->sort(/* Copilot suggests sort options */)
    ->size(/* Copilot suggests common sizes like 10, 20, 50 */)
    ->build();
```

### 3. Use Variable Names That Trigger Context
```php
// Variables that help Copilot understand context
$productQuery = new ElasticQuery();
$blogQuery = new ElasticQuery();  
$geoQuery = new ElasticQuery();
$analyticsQuery = new ElasticQuery();
```

### 4. Common Variable Patterns
```php
// Search terms
$searchTerm = 'laptop';
$userQuery = 'best smartphones';

// Filters
$category = 'electronics';
$status = 'published';
$minPrice = 100;
$maxPrice = 1000;

// Location
$userLocation = '40.7128,-74.0060';
$distance = '5km';

// Pagination
$page = 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;
```

## 🚀 Advanced Copilot Patterns

### Debug Mode Pattern
```php
// Enable debugging for development
$query = new ElasticQuery();
$query->verbose(); // Copilot suggests debugging methods

$result = $query
    // ... your query
    ->build();

// Copilot suggests these debug patterns:
foreach ($query->getVerboseLog() as $log) {
    echo $log . "\n";
}

foreach ($query->getWarnings() as $warning) {
    echo "⚠️ " . $warning . "\n";
}
```

### Error Handling Pattern
```php
try {
    $query = new ElasticQuery();
    $result = $query
        // ... your query
        ->build();
        
    $response = $client->search([
        'index' => 'my_index',
        'body' => $result
    ]);
} catch (Exception $e) {
    // Copilot suggests error handling
    $readable = $query->translateError($e->getMessage());
    echo "Error: " . $readable;
}
```

### Performance Optimization Pattern
```php
// Performance-conscious query building
$query = new ElasticQuery();
$query->verbose(); // Monitor performance

$result = $query
    ->filter('status', 'published') // Filter first (faster)
    ->search($term, ['title^3', 'content']) // Then search
    ->range('created_at', 'gte', 'now-1y') // Limit time range
    ->size(20) // Reasonable page size
    ->source(['id', 'title', 'summary']) // Limit returned fields
    ->build();

// Check for performance warnings
$warnings = $query->getPerformanceWarnings();
```

## 📚 IDE Integration Tips

### VS Code with GitHub Copilot
1. Install the GitHub Copilot extension
2. QueryCraft's PHPDoc comments provide rich context
3. Use descriptive variable names and comments
4. Let Copilot suggest method chains based on context

### Example Workflow
```php
<?php
// 1. Start with a comment describing your intent
// "Search for electronics products under $500 with brand aggregations"

// 2. Create query instance - Copilot will suggest the rest
$query = new ElasticQuery();

// 3. Copilot suggests appropriate method chain:
$result = $query
    ->search('electronics', ['name^3', 'description'])
    ->range('price', 'lte', 500)
    ->filter('status', 'active')
    ->aggregation('brands', [
        'terms' => ['field' => 'brand.keyword', 'size' => 10]
    ])
    ->sort('popularity', 'desc')
    ->size(20)
    ->build();
```

---

**💡 Pro Tip**: The more context you provide through comments and variable names, the better GitHub Copilot will understand your intent and suggest accurate QueryCraft methods.
