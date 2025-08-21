# 🌟 QueryCraft

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Composer](https://img.shields.io/badge/Composer-Ready-orange.svg)](composer.json)
[![Tests](https://img.shields.io/badge/Tests-100%25-brightgreen.svg)](docs/TESTING.md)
[![Laravel Compatible](https://img.shields.io/badge/Laravel-Compatible-red.svg)](https://laravel.com)
[![Eloquent Friendly](https://img.shields.io/badge/Eloquent-Friendly-purple.svg)](#-for-laravel-developers)
[![GitHub Stars](https://img.shields.io/github/stars/arthur2weber/query-craft?style=social)](https://github.com/arthur2weber/query-craft)

> **Elegant Elasticsearch DSL Query Builder for PHP** 
>
> Craft complex Elasticsearch queries with Laravel-inspired fluent syntax. Zero dependencies, maximum productivity.

## 🚀 Quick Start

```bash
composer require arthur2weber/query-craft
```

```php
<?php
use Arthur2weber\QueryCraft\ElasticQuery;

// Simple search with filters
$query = new ElasticQuery();
$dslQuery = $query
    ->search('PHP Elasticsearch', ['title^3', 'content'])
    ->filter('status', 'published')
    ->range('created_at', 'gte', '2024-01-01')
    ->sort('_score', 'desc')
    ->size(10)
    ->build();

// Ready for Elasticsearch PHP Client
$response = $client->search([
    'index' => 'articles',
    'body' => $dslQuery
]);
```

## ✨ Why QueryCraft?

- **🎯 Familiar Syntax** - Laravel-like methods for instant productivity
- **⚡ Zero Dependencies** - Pure PHP, no framework requirements  
- **🔧 Complete DSL Support** - Full Elasticsearch 7.x+ compatibility
- **📊 Advanced Features** - Aggregations, geo-queries, nested objects
- **🛡️ Developer Experience** - Verbose mode, error translation, performance warnings
- **📚 Rich Documentation** - 50+ examples and comprehensive guides
- **💎 Eloquent-Friendly** - **Zero learning curve for Laravel developers**

## 🚀 For Laravel Developers

**If you know Eloquent, you already know QueryCraft!** 

Use the exact same methods you love:

```php
// ✅ Your familiar Eloquent syntax
$users = User::where('status', 'active')
            ->whereIn('role', ['admin', 'editor'])
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->paginate(15);

// ✅ Identical QueryCraft syntax
$docs = (new ElasticQuery())
       ->where('status', 'active')
       ->whereIn('role', ['admin', 'editor'])
       ->whereBetween('created_at', [$start, $end])
       ->orderByDesc('created_at')
       ->paginate(15);
```

**Everything works exactly as expected:**
- `where()`, `whereIn()`, `whereNotIn()`, `whereBetween()`
- `orderBy()`, `orderByDesc()`, `latest()`, `oldest()`
- `paginate()`, `limit()`, `offset()`, `take()`, `skip()`
- `when()`, `unless()` - conditional queries
- `count()`, `avg()`, `sum()`, `max()`, `min()` - aggregations

**Plus powerful Elasticsearch features:**
- Full-text search with `search()` and `match()`
- Geographic queries with `geoDistance()` and `geoBoundingBox()`
- Advanced aggregations and faceted search
- Fuzzy search and auto-complete

## 🔥 Core Features

### 🔍 **Search & Query Methods**
```php
// Text search with boost
->search('PHP Laravel', ['title^3', 'content', 'tags^2'])
->match('title', 'Elasticsearch tutorial')
->matchPhrase('content', 'exact phrase search')
->queryString('title:PHP AND status:published')

// Term & filters  
->filter('status', 'published')
->terms('category', ['php', 'elasticsearch'])
->range('price', 'gte', 100)
->exists('featured_image')

// Advanced search
->fuzzy('title', 'elesticsearch', 2)  // 2 typos allowed
->wildcard('filename', '*.php')
->regexp('code', '[a-z]+@[a-z]+')
```

### 📊 **Aggregations & Analytics**
```php
// Terms aggregation (categories)
->aggregation('categories', [
    'terms' => ['field' => 'category.keyword', 'size' => 10]
])

// Date histogram (time series)
->aggregation('monthly_posts', [
    'date_histogram' => [
        'field' => 'created_at',
        'calendar_interval' => 'month'
    ]
])

// Nested aggregations with statistics
->aggregation('categories', [
    'terms' => [
        'field' => 'category.keyword',
        'aggs' => [
            'avg_price' => ['avg' => ['field' => 'price']],
            'max_views' => ['max' => ['field' => 'views']]
        ]
    ]
])
```

### 🌍 **Geographic Search**
```php
// Find restaurants within 5km
->geoDistance('location', '40.7128,-74.0060', '5km')

// Bounding box search
->geoBoundingBox('location', [
    'top_left' => '40.8,-74.1',
    'bottom_right' => '40.6,-73.9'
])

// Sort by distance
->sort([
    '_geo_distance' => [
        'location' => '40.7128,-74.0060',
        'order' => 'asc'
    ]
])
```

### 🛡️ **Developer Experience**
```php
// Enable debugging
$query->verbose();

// Get detailed logs
$logs = $query->getVerboseLog();

// Performance warnings
$warnings = $query->getPerformanceWarnings();

// Human-readable error translation
$readable = $query->translateError($elasticsearchError);
```

## 🚀 Quick Examples

### Laravel Migration (Zero Learning Curve!)
```php
// Before (Eloquent)
$posts = Post::where('status', 'published')
             ->whereIn('category', ['tech', 'programming'])
             ->orderByDesc('created_at')
             ->paginate(15);

// After (QueryCraft) - Identical syntax!
$posts = (new ElasticQuery())
        ->where('status', 'published')
        ->whereIn('category', ['tech', 'programming'])
        ->orderByDesc('created_at')
        ->paginate(15);

// Enhanced with full-text search
$posts = (new ElasticQuery())
        ->search('Laravel tutorial', ['title^3', 'content']) // ✨ New superpower
        ->where('status', 'published')
        ->whereIn('category', ['tech', 'programming'])
        ->orderByDesc('_score')  // ✨ Sort by relevance
        ->paginate(15);
```

### Basic Search
```php
// Simple product search with filters
$query = new ElasticQuery();
$result = $query
    ->search('iPhone 13', ['title^3', 'description'])
    ->filter('status', 'active')
    ->filter('in_stock', true)
    ->range('price', 'lte', 1000)
    ->sort(['_score' => 'desc', 'price' => 'asc'])
    ->size(20)
    ->build();
```

### E-commerce with Facets
```php
// Product search with category aggregations
$query = new ElasticQuery();
$result = $query
    ->search($searchTerm, ['name^3', 'brand^2', 'description'])
    ->filter('status', 'active')
    ->range('price', 'gte', $minPrice)
    ->aggregation('brands', [
        'terms' => ['field' => 'brand.keyword', 'size' => 10]
    ])
    ->aggregation('categories', [
        'terms' => ['field' => 'category.keyword', 'size' => 5]
    ])
    ->sort('_score', 'desc')
    ->build();
```

### Geographic Search
```php
// Find nearby restaurants
$query = new ElasticQuery();
$result = $query
    ->match('type', 'restaurant')
    ->geoDistance('location', '40.7128,-74.0060', '2km')
    ->filter('rating', '>=', 4.0)
    ->sort([
        '_geo_distance' => [
            'location' => '40.7128,-74.0060',
            'order' => 'asc'
        ]
    ])
    ->build();
```

## 📚 Documentation

- **[📖 Complete Wiki](docs/WIKI.md)** - Comprehensive usage guide with examples
- **[⚡ Quick Reference](docs/quick-reference.md)** - All methods and patterns  
- **[🔧 Developer Experience](docs/developer-experience.md)** - Debugging and optimization
- **[📊 Testing Guide](docs/TESTING.md)** - Test suite and quality metrics
- **[📈 Test Coverage Summary](docs/TEST-COVERAGE-SUMMARY.md)** - Detailed test statistics and coverage
- **[🗺️ Roadmap](docs/ROADMAP.md)** - Future development plans

## 🧪 Testing

```bash
# Quick tests (recommended for development)
./tt

# All tests  
./tt all

# Specific categories
./tt core search filters utilities

# Alternative methods
php t.php
make test
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](docs/CONTRIBUTING.md) for details.

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Run tests: `./tt all`
4. Commit changes: `git commit -m 'Add amazing feature'`
5. Push to branch: `git push origin feature/amazing-feature`
6. Open a Pull Request

## 📄 License

This project is licensed under the [MIT License](LICENSE).

## 🔗 Links

- **GitHub Repository**: [arthur2weber/query-craft](https://github.com/arthur2weber/query-craft)
- **Packagist**: [arthur2weber/query-craft](https://packagist.org/packages/arthur2weber/query-craft)
- **Issues**: [Report bugs or request features](https://github.com/arthur2weber/query-craft/issues)

---

**Made with ❤️ for the PHP community**
