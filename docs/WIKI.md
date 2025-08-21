# 📖 QueryCraft - Complete Wiki Documentation

> **Comprehensive reference guide with all methods, examples, and use cases**
>
> This wiki contains everything you need to master QueryCraft Elasticsearch DSL Query Builder.

## 📋 Table of Contents

1. [💎 For Laravel Developers](#-for-laravel-developers)
2. [Getting Started](#-getting-started)
3. [Text Search Methods](#-text-search-methods)
4. [Filter & Term Methods](#-filter--term-methods)
5. [Boolean Logic Methods](#-boolean-logic-methods)
6. [Range & Comparison Methods](#-range--comparison-methods)
7. [Geographic Methods](#-geographic-methods)
8. [Fuzzy & Advanced Search](#-fuzzy--advanced-search)
9. [Aggregation Methods](#-aggregation-methods)
10. [Sorting & Pagination](#-sorting--pagination)
11. [Utility Methods](#-utility-methods)
12. [Developer Experience](#-developer-experience)
13. [Validation & Error Handling](#-validation--error-handling)
14. [Real-World Examples](#-real-world-examples)
15. [Performance Best Practices](#-performance-best-practices)

---

## 💎 For Laravel Developers

**If you know Eloquent, you already know QueryCraft!** This section shows how QueryCraft uses the exact same syntax you're familiar with from Laravel Eloquent.

### Zero Learning Curve

**Your Eloquent knowledge transfers 100%:**

```php
// ✅ Your familiar Eloquent queries
$users = User::where('status', 'active')
            ->whereIn('role', ['admin', 'editor'])
            ->whereBetween('age', [18, 65])
            ->whereNotNull('email_verified_at')
            ->orderByDesc('created_at')
            ->latest('updated_at')
            ->paginate(20);

// ✅ Identical QueryCraft syntax
$docs = (new ElasticQuery())
       ->where('status', 'active')
       ->whereIn('role', ['admin', 'editor'])
       ->whereBetween('age', [18, 65])
       ->whereNotNull('email_verified_at')
       ->orderByDesc('created_at')
       ->latest('updated_at')
       ->paginate(20);
```

**Every Eloquent method you love works exactly the same way.**

---

### Complete Eloquent Method Support

#### 🔍 **Where Clauses (100% Compatible)**

```php
// All your favorite where methods
$query = new ElasticQuery();
$result = $query
    // Basic where clauses
    ->where('status', 'published')           // Exact match
    ->where('price', '>', 100)               // Comparison operators
    ->where('rating', '>=', 4.5)             // All operators: =, !=, >, >=, <, <=
    
    // Array-based filtering
    ->whereIn('category', ['tech', 'science', 'programming'])
    ->whereNotIn('status', ['draft', 'deleted', 'spam'])
    
    // Range filtering
    ->whereBetween('created_at', ['2024-01-01', '2024-12-31'])
    ->whereBetween('price', [50, 500])
    
    // Null checks
    ->whereNotNull('published_at')           // Field must exist
    ->whereNull('deleted_at')                // Field must not exist
    
    ->build();
```

#### 📊 **Sorting (100% Compatible)**

```php
// All your familiar sorting methods
$query = new ElasticQuery();
$result = $query
    ->search('Laravel tutorial', ['title^2', 'content'])
    
    // Basic sorting
    ->orderBy('created_at', 'desc')          // Basic sort
    ->orderByDesc('views')                   // Descending shortcut
    
    // Date sorting shortcuts
    ->latest('published_at')                 // Most recent first
    ->oldest('created_at')                   // Oldest first
    
    ->build();
```

#### 📄 **Pagination (100% Compatible)**

```php
// Exactly like Laravel pagination
$query = new ElasticQuery();
$result = $query
    ->search('products', ['name^3', 'description'])
    ->where('in_stock', true)
    
    // Laravel-style pagination
    ->paginate(20, 3)                       // 20 per page, page 3
    ->paginate()                            // Default: 15 per page, page 1
    
    // Alternative syntax
    ->limit(10)                             // Alias for size(10)
    ->offset(20)                            // Alias for from(20)
    ->take(15)                              // Alias for size(15)
    ->skip(30)                              // Alias for from(30)
    
    ->build();
```

#### 🔄 **Conditional Queries (100% Compatible)**

```php
// Your beloved when() and unless() methods
$userRole = 'admin';
$showDrafts = true;
$featured = false;

$query = new ElasticQuery();
$result = $query
    ->search('articles', ['title^3', 'content'])
    
    // Conditional logic exactly like Eloquent
    ->when($userRole === 'admin', function($q) {
        return $q->whereIn('status', ['published', 'draft', 'pending']);
    })
    ->when($userRole !== 'admin', function($q) {
        return $q->where('status', 'published');
    })
    ->unless($userRole === 'admin', function($q) {
        return $q->where('visibility', 'public');
    })
    ->when($showDrafts, function($q) {
        return $q->whereIn('status', ['published', 'draft']);
    })
    ->when($featured, function($q) {
        return $q->where('featured', true);
    })
    
    ->build();
```

#### 📈 **Aggregations (100% Compatible)**

```php
// Laravel-style aggregation methods
$query = new ElasticQuery();
$result = $query
    ->where('status', 'completed')
    ->whereBetween('order_date', ['2024-01-01', '2024-12-31'])
    
    // Familiar aggregation methods
    ->count()                               // Total count
    ->avg('price')                          // Average price
    ->sum('total_amount')                   // Sum of amounts
    ->max('rating')                         // Maximum rating
    ->min('price')                          // Minimum price
    
    ->build();
```

---

### Eloquent Scopes Pattern

QueryCraft supports Laravel's scope pattern for reusable query logic:

```php
// Predefined scopes (exactly like Eloquent)
$query = new ElasticQuery();
$result = $query
    // Common scopes
    ->active()                              // where('status', 'active')
    ->published()                           // where('status', 'published') + published_at check
    ->recent(30)                            // created in last 30 days
    
    // Chainable like Eloquent
    ->active()
    ->published()
    ->recent(7)
    ->latest()
    ->paginate(15);
```

---

### Migration From Eloquent

**Step 1:** Replace your Model with ElasticQuery
```php
// Before (Eloquent)
$posts = Post::where('status', 'published')
             ->whereIn('category', ['tech', 'programming'])
             ->orderByDesc('created_at')
             ->paginate(15);

// After (QueryCraft) - Just change the first line!
$posts = (new ElasticQuery())
        ->where('status', 'published')
        ->whereIn('category', ['tech', 'programming'])
        ->orderByDesc('created_at')
        ->paginate(15);
```

**Step 2:** Add Elasticsearch-specific features
```php
// Enhanced with full-text search
$posts = (new ElasticQuery())
        ->search('Laravel tutorial', ['title^3', 'content'])  // 🆕 Full-text search
        ->where('status', 'published')
        ->whereIn('category', ['tech', 'programming'])
        ->orderByDesc('created_at')
        ->paginate(15);
```

**Step 3:** Add advanced features as needed
```php
// With faceted search and aggregations
$posts = (new ElasticQuery())
        ->search('Laravel tutorial', ['title^3', 'content'])
        ->where('status', 'published')
        ->whereIn('category', ['tech', 'programming'])
        
        // 🆕 Elasticsearch superpowers
        ->aggregation('categories', [
            'terms' => ['field' => 'category.keyword', 'size' => 10]
        ])
        ->aggregation('monthly_posts', [
            'date_histogram' => ['field' => 'created_at', 'calendar_interval' => 'month']
        ])
        
        ->orderByDesc('_score')             // 🆕 Sort by relevance
        ->orderByDesc('created_at')
        ->paginate(15);
```

---

### Real-World Laravel Migration Example

**Before (Laravel Controller with Eloquent):**
```php
class PostController extends Controller 
{
    public function index(Request $request)
    {
        $posts = Post::query()
            ->when($request->search, function($q) use ($request) {
                return $q->where('title', 'like', '%' . $request->search . '%')
                         ->orWhere('content', 'like', '%' . $request->search . '%');
            })
            ->when($request->category, function($q) use ($request) {
                return $q->where('category', $request->category);
            })
            ->when($request->author, function($q) use ($request) {
                return $q->where('author_id', $request->author);
            })
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->paginate(15);
            
        return view('posts.index', compact('posts'));
    }
}
```

**After (Same Controller with QueryCraft):**
```php
class PostController extends Controller 
{
    public function index(Request $request)
    {
        $query = new ElasticQuery();
        
        $posts = $query
            // 🆕 Better search with relevance scoring
            ->when($request->search, function($q) use ($request) {
                return $q->search($request->search, ['title^3', 'content', 'tags^2']);
            })
            ->when($request->category, function($q) use ($request) {
                return $q->where('category', $request->category);
            })
            ->when($request->author, function($q) use ($request) {
                return $q->where('author_id', $request->author);
            })
            ->where('status', 'published')
            
            // 🆕 Add faceted search for better UX
            ->aggregation('categories', [
                'terms' => ['field' => 'category.keyword', 'size' => 10]
            ])
            ->aggregation('authors', [
                'terms' => ['field' => 'author.keyword', 'size' => 10]
            ])
            
            // 🆕 Better sorting with relevance
            ->when($request->search, function($q) {
                return $q->orderByDesc('_score');
            })
            ->orderByDesc('created_at')
            ->paginate(15);
            
        return view('posts.index', compact('posts'));
    }
}
```

**Key improvements with zero syntax changes:**
- ✅ Same familiar Eloquent methods
- ✅ Better full-text search with relevance scoring
- ✅ Faceted search for improved UX
- ✅ More flexible and powerful filtering
- ✅ All your existing conditional logic works unchanged

---

### 💡 Key Benefits for Laravel Developers

**QueryCraft leverages your existing Eloquent knowledge:**

1. **Zero Learning Curve** - All your familiar methods work identically
2. **Progressive Enhancement** - Start with Eloquent syntax, add Elasticsearch superpowers
3. **Same Patterns** - Conditional queries, scopes, pagination work exactly as expected
4. **Better Performance** - Elasticsearch speed with Laravel syntax familiarity

### Laravel Controller Example

Here's how you can migrate a Laravel controller from Eloquent to QueryCraft:

```php
// Before: Laravel Controller with Eloquent
class PostController extends Controller
{
    public function search(Request $request)
    {
        $posts = Post::query()
            ->when($request->search, function($q) use ($request) {
                return $q->where('title', 'like', '%' . $request->search . '%')
                         ->orWhere('content', 'like', '%' . $request->search . '%');
            })
            ->when($request->category, function($q) use ($request) {
                return $q->where('category', $request->category);
            })
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->paginate(15);
            
        return view('posts.search', compact('posts'));
    }
}

// After: Same Controller with QueryCraft (enhanced)
class PostController extends Controller
{
    public function search(Request $request)
    {
        $query = new ElasticQuery();
        $posts = $query
            // ✨ Better search with relevance scoring
            ->when($request->search, function($q) use ($request) {
                return $q->search($request->search, ['title^3', 'content', 'tags^2']);
            })
            ->when($request->category, function($q) use ($request) {
                return $q->where('category', $request->category);
            })
            ->where('status', 'published')
            
            // ✨ Add faceted search
            ->aggregation('categories', [
                'terms' => ['field' => 'category.keyword', 'size' => 10]
            ])
            ->aggregation('tags', [
                'terms' => ['field' => 'tags.keyword', 'size' => 15]
            ])
            
            // ✨ Smart sorting
            ->when($request->search, function($q) {
                return $q->orderByDesc('_score');
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->build();
            
        // Use with Elasticsearch client
        $response = $elasticsearchClient->search([
            'index' => 'posts',
            'body' => $posts
        ]);
        
        return view('posts.search', [
            'posts' => $response['hits']['hits'],
            'aggregations' => $response['aggregations'] ?? []
        ]);
    }
}
```

**Key improvements with QueryCraft:**
- **Better Search**: Full-text search with field boosting instead of LIKE queries
- **Relevance Scoring**: Sort by `_score` for better result ranking
- **Faceted Search**: Get category and tag counts for filtering UI
- **Performance**: Elasticsearch speed vs database LIKE queries
- **Same Logic**: `when()` conditions work identically

📖 **[See complete migration examples →](../examples/laravel-migration.php)**

### Why QueryCraft for Laravel Developers?

1. **🎯 Zero Learning Curve** - Use your existing Eloquent knowledge
2. **⚡ Drop-in Replacement** - Change one line, keep all your logic
3. **🚀 Enhanced Power** - Add Elasticsearch features incrementally
4. **🔧 Same Patterns** - Scopes, conditions, pagination all work the same
5. **📚 Familiar Docs** - Documentation written for Laravel developers
6. **🛠️ Laravel Integration** - Works perfectly in Laravel apps

**Start using QueryCraft today - your Eloquent skills are your QueryCraft skills!**

---

## 🚀 Getting Started

### Installation

```bash
composer require arthur2weber/query-craft
```

### Basic Usage Pattern

```php
<?php
use Arthur2weber\QueryCraft\ElasticQuery;

// Standard pattern for all queries
$query = new ElasticQuery();
$result = $query
    ->method1(/* parameters */)
    ->method2(/* parameters */)
    ->method3(/* parameters */)
    ->build(); // Returns array ready for Elasticsearch

// Use with Elasticsearch PHP Client
$response = $client->search([
    'index' => 'your_index',
    'body' => $result
]);
```

### Method Chaining Rules

- ✅ **Immutable**: Each method returns a new instance for chaining
- ✅ **Fluent**: Methods can be chained in any logical order
- ✅ **Validating**: Parameters are validated automatically
- ✅ **Verbose**: Enable `->verbose()` for debugging

---

## 🔍 Text Search Methods

Text search methods for full-text search capabilities with analysis and scoring.

### `match($field, $value)`

Basic text search with analysis - most commonly used for full-text search.

```php
// Basic match query
$query = new ElasticQuery();
$result = $query
    ->match('title', 'Laravel PHP')
    ->build();

// Generated Elasticsearch DSL:
{
    "query": {
        "bool": {
            "must": [
                {"match": {"title": "Laravel PHP"}}
            ]
        }
    }
}
```

**Use Cases:**
- Blog post titles
- Product names
- Article content
- User-generated content

**Parameters:**
- `$field` (string): Field name to search in
- `$value` (string): Text to search for

---

### `search($query, $fields = ['_all'], $boost = 1.0)`

Multi-field search with field boosting - perfect for relevance tuning.

```php
// Multi-field search with different boosts
$query = new ElasticQuery();
$result = $query
    ->search('Laravel tutorial', [
        'title^3',      // Title gets 3x boost
        'content^1',    // Content gets normal weight
        'tags^2'        // Tags get 2x boost
    ])
    ->build();

// E-commerce product search example
$productQuery = new ElasticQuery();
$result = $productQuery
    ->search('wireless headphones', [
        'name^5',           // Product name most important
        'brand^3',          // Brand quite important  
        'description^1',    // Description normal weight
        'features^2'        // Features moderately important
    ])
    ->build();
```

**Use Cases:**
- E-commerce product search
- Content management systems
- Documentation search
- Multi-field relevance tuning

**Parameters:**
- `$query` (string): Search query text
- `$fields` (array): Fields to search with optional boost (e.g., 'field^2')
- `$boost` (float): Overall query boost (default: 1.0)

---

### `matchPhrase($field, $phrase)`

Exact phrase matching - searches for words in exact order.

```php
// Find exact phrase
$query = new ElasticQuery();
$result = $query
    ->matchPhrase('content', 'machine learning algorithms')
    ->build();

// Real-world example: Finding specific error messages
$errorQuery = new ElasticQuery();
$result = $errorQuery
    ->matchPhrase('error_message', 'connection timeout')
    ->filter('severity', 'error')
    ->build();
```

**Use Cases:**
- Error message searches
- Exact quote searches
- Technical documentation
- Legal document searches

**Parameters:**
- `$field` (string): Field to search in
- `$phrase` (string): Exact phrase to match

---

### `searchPhrase($field, $phrase)`

Alias for `matchPhrase()` - same functionality with different naming.

```php
// Alternative syntax for phrase matching
$query = new ElasticQuery();
$result = $query
    ->searchPhrase('description', 'step by step guide')
    ->build();
```

---

### `queryString($query)`

Advanced query string with operators - supports AND, OR, NOT, wildcards.

```php
// Complex query string
$query = new ElasticQuery();
$result = $query
    ->queryString('title:(Laravel OR Symfony) AND status:published NOT draft')
    ->build();

// Search with wildcards and fields
$advancedQuery = new ElasticQuery();
$result = $advancedQuery
    ->queryString('author:john* AND (title:PHP OR content:Laravel)')
    ->build();
```

**Use Cases:**
- Advanced search interfaces
- Developer tools
- Complex filtering needs
- Power user features

**Supported Operators:**
- `AND`, `OR`, `NOT`
- `+` (must match), `-` (must not match)
- `*` (wildcard), `?` (single character)
- `"phrase"` (phrase search)
- `field:value` (field-specific search)

---

### `matchBoost($field, $value, $boost)`

Match query with custom boost for relevance tuning.

```php
// Boost important fields
$query = new ElasticQuery();
result = $query
    ->matchBoost('title', 'Laravel', 3.0)        // 3x more important
    ->matchBoost('content', 'Laravel', 1.0)      // Normal weight
    ->matchBoost('tags', 'Laravel', 2.0)         // 2x more important
    ->build();
```

**Use Cases:**
- Fine-tuning search relevance
- A/B testing different weights
- Custom scoring models
- Business logic prioritization

**Parameters:**
- `$field` (string): Field to search
- `$value` (string): Value to match
- `$boost` (float): Boost multiplier

---

## 🔧 Filter & Term Methods

Filter methods for exact matches and efficient filtering without scoring.

### `filter($field, $value)` or `filter($condition)`

Efficient filtering without affecting relevance score.

```php
// Simple field-value filter
$query = new ElasticQuery();
$result = $query
    ->search('Laravel tutorial', ['title^2', 'content'])
    ->filter('status', 'published')
    ->filter('category', 'programming')
    ->build();

// Complex filter condition
$complexQuery = new ElasticQuery();
$result = $complexQuery
    ->search('headphones', ['name^3', 'description'])
    ->filter([
        'range' => [
            'price' => ['gte' => 50, 'lte' => 200]
        ]
    ])
    ->build();
```

**Performance Note:** Filters are cached and don't affect scoring, making them very fast.

---

### `term($field, $value)`

Exact term matching - no analysis, perfect for IDs, keywords, booleans.

```php
// Exact matches
$query = new ElasticQuery();
$result = $query
    ->term('status', 'active')           // Status exactly "active"
    ->term('featured', true)             // Boolean true
    ->term('category_id', 123)           // Numeric ID
    ->term('user.keyword', 'john_doe')   // Exact username
    ->build();
```

**Use Cases:**
- IDs and primary keys
- Status fields
- Boolean flags
- Exact keyword matching
- Category selections

---

### `terms($field, $values)`

Multiple term matching - like SQL IN clause.

```php
// Multiple values
$query = new ElasticQuery();
$result = $query
    ->terms('category', ['php', 'laravel', 'symfony'])
    ->terms('status', ['published', 'featured'])
    ->build();

// E-commerce example
$productQuery = new ElasticQuery();
$result = $productQuery
    ->search('laptop', ['name^3', 'description'])
    ->terms('brand', ['apple', 'dell', 'hp', 'lenovo'])
    ->terms('color', ['black', 'silver', 'white'])
    ->build();
```

**Use Cases:**
- Multi-select filters
- Category filtering
- Brand selection
- Status filtering
- Tag matching

---

### `exists($field)`

Check if field exists and has a non-null value.

```php
// Check for field existence
$query = new ElasticQuery();
$result = $query
    ->exists('featured_image')       // Must have featured image
    ->exists('author.email')         // Must have author email
    ->filter('status', 'published')
    ->build();
```

**Use Cases:**
- Data quality checks
- Optional field filtering
- Complete profile requirements
- Media presence validation

---

### `prefix($field, $prefix)`

Prefix matching for auto-complete and starts-with searches.

```php
// Auto-complete functionality
$query = new ElasticQuery();
$result = $query
    ->prefix('city', 'san')          // San Francisco, San Diego, etc.
    ->prefix('product_code', 'LAP')  // LAP001, LAP002, etc.
    ->build();
```

**Use Cases:**
- Auto-complete features
- Product code searches
- Location searches
- Category hierarchies

---

## 📊 Boolean Logic Methods

Combine multiple conditions with AND, OR, NOT logic.

### `must($condition)`

Required condition (AND logic) - all must match.

```php
// All conditions must match
$query = new ElasticQuery();
$result = $query
    ->must(ElasticQuery::matchClause('title', 'Laravel'))
    ->must(ElasticQuery::termClause('status', 'published'))
    ->must([
        'range' => [
            'created_at' => ['gte' => '2024-01-01']
        ]
    ])
    ->build();
```

---

### `should($condition)`

Optional condition (OR logic) - at least one should match.

```php
// At least one condition should match
$query = new ElasticQuery();
$result = $query
    ->filter('status', 'published')
    ->should(ElasticQuery::matchClause('tags', 'tutorial'))
    ->should(ElasticQuery::matchClause('tags', 'beginner'))
    ->should(ElasticQuery::termClause('featured', true))
    ->minimumShouldMatch(1)  // At least 1 should condition must match
    ->build();
```

---

### `mustNot($condition)`

Exclusion condition (NOT logic) - must not match.

```php
// Exclude certain conditions
$query = new ElasticQuery();
$result = $query
    ->search('PHP tutorial', ['title^2', 'content'])
    ->mustNot(ElasticQuery::termClause('status', 'draft'))
    ->mustNot(ElasticQuery::termClause('private', true))
    ->mustNot([
        'range' => [
            'created_at' => ['lt' => '2023-01-01']
        ]
    ])
    ->build();
```

---

### Static Clause Methods

Helper methods for creating reusable query fragments.

```php
// Create reusable clauses
$publishedClause = ElasticQuery::termClause('status', 'published');
$recentClause = ElasticQuery::rangeClause('created_at', 'gte', '2024-01-01');
$titleMatch = ElasticQuery::matchClause('title', 'Laravel');

// Use in different queries
$query1 = new ElasticQuery();
$result1 = $query1
    ->must($publishedClause)
    ->must($recentClause)
    ->must($titleMatch)
    ->build();

$query2 = new ElasticQuery();
$result2 = $query2
    ->should($titleMatch)
    ->filter($publishedClause)
    ->build();
```

**Available Static Methods:**
- `ElasticQuery::matchClause($field, $value)`
- `ElasticQuery::termClause($field, $value)`
- `ElasticQuery::rangeClause($field, $operator, $value)`
- `ElasticQuery::termsClause($field, $values)`
- `ElasticQuery::existsClause($field)`
- `ElasticQuery::termBoostClause($field, $value, $boost)`

---

## 📏 Range & Comparison Methods

Range and comparison methods for numeric, date, and string range queries.

### `range($field, $operator, $value)`

Flexible range queries with multiple operators.

```php
// Numeric ranges
$query = new ElasticQuery();
$result = $query
    ->search('laptop', ['name^3', 'description'])
    ->range('price', 'gte', 500)        // Price >= 500
    ->range('price', 'lte', 2000)       // Price <= 2000
    ->range('rating', 'gt', 4.0)        // Rating > 4.0
    ->build();

// Date ranges
$blogQuery = new ElasticQuery();
$result = $blogQuery
    ->search('tutorial', ['title^2', 'content'])
    ->range('published_at', 'gte', '2024-01-01')
    ->range('published_at', 'lt', '2024-12-31')
    ->build();

// String ranges (alphabetical)
$authorQuery = new ElasticQuery();
$result = $authorQuery
    ->range('author.keyword', 'gte', 'A')
    ->range('author.keyword', 'lt', 'M')
    ->build();
```

**Supported Operators:**
- `gte` - Greater than or equal (>=)
- `gt` - Greater than (>)
- `lte` - Less than or equal (<=)
- `lt` - Less than (<)

**Use Cases:**
- Price filtering
- Date range filtering
- Rating/score filtering
- Age restrictions
- Alphabetical ranges

---

### Laravel-Style Where Methods

Familiar Laravel Eloquent syntax for easier migration.

### `where($field, $operator, $value)` or `where($field, $value)`

```php
// Laravel-style where clauses
$query = new ElasticQuery();
$result = $query
    ->where('status', 'published')           // Exact match
    ->where('price', '>=', 100)              // Range with operator
    ->where('rating', '>', 4.0)              // Greater than
    ->where('category', '!=', 'draft')       // Not equal
    ->build();

// E-commerce filtering example
$productQuery = new ElasticQuery();
$result = $productQuery
    ->search('smartphone', ['name^3', 'description'])
    ->where('in_stock', true)
    ->where('price', '>=', 200)
    ->where('price', '<=', 1000)
    ->where('brand', '!=', 'unknown')
    ->build();
```

**Supported Operators:**
- `=` or no operator - Exact match
- `!=`, `<>` - Not equal
- `>`, `>=`, `<`, `<=` - Comparisons
- `like` - Wildcard matching
- `not like` - Negative wildcard

---

### `whereIn($field, $values)`

Multiple value matching (like SQL IN).

```php
// Multiple value filtering
$query = new ElasticQuery();
$result = $query
    ->search('tutorial', ['title^2', 'content'])
    ->whereIn('category', ['php', 'laravel', 'symfony'])
    ->whereIn('difficulty', ['beginner', 'intermediate'])
    ->whereIn('author_id', [1, 5, 10, 15])
    ->build();
```

---

### `whereNotIn($field, $values)`

Exclude multiple values (like SQL NOT IN).

```php
// Exclude multiple values
$query = new ElasticQuery();
$result = $query
    ->search('product', ['name^3', 'description'])
    ->whereNotIn('status', ['draft', 'archived', 'deleted'])
    ->whereNotIn('category', ['restricted', 'private'])
    ->build();
```

---

### `whereBetween($field, [$min, $max])`

Range between two values (inclusive).

```php
// Between ranges
$query = new ElasticQuery();
$result = $query
    ->search('hotel', ['name^3', 'description'])
    ->whereBetween('price', [100, 300])          // Price between 100-300
    ->whereBetween('rating', [4.0, 5.0])         // Rating between 4-5
    ->whereBetween('rooms', [1, 4])              // 1-4 rooms
    ->build();

// Date range example
$eventQuery = new ElasticQuery();
$result = $eventQuery
    ->search('conference', ['title^3', 'description'])
    ->whereBetween('event_date', ['2024-06-01', '2024-08-31'])
    ->build();
```

---

### `whereNotNull($field)`

Field must exist and have a value.

```php
// Required fields
$query = new ElasticQuery();
$result = $query
    ->search('profile', ['name^2', 'bio'])
    ->whereNotNull('email')              // Email required
    ->whereNotNull('profile_image')      // Profile image required
    ->whereNotNull('verified_at')        // Must be verified
    ->build();
```

---

### `whereNull($field)`

Field must not exist or be null.

```php
// Missing or null fields
$query = new ElasticQuery();
$result = $query
    ->where('status', 'active')
    ->whereNull('deleted_at')            // Not deleted
    ->whereNull('banned_at')             // Not banned
    ->build();
```

---

## 🌍 Geographic Methods

Geospatial search methods for location-based queries.

### `geoDistance($field, $location, $distance)`

Find documents within a certain distance from a point.

```php
// Find restaurants within 5km
$query = new ElasticQuery();
$result = $query
    ->search('italian restaurant', ['name^3', 'cuisine'])
    ->geoDistance('location', '40.7128,-74.0060', '5km')  // NYC coordinates
    ->filter('rating', '>=', 4.0)
    ->build();

// Multiple location formats supported
$query2 = new ElasticQuery();
$result2 = $query2
    ->geoDistance('location', [40.7128, -74.0060], '10km')    // Array format
    ->build();

$query3 = new ElasticQuery();
result3 = $query3
    ->geoDistance('location', ['lat' => 40.7128, 'lon' => -74.0060], '2mi')  // Object format
    ->build();
```

**Distance Units:**
- `km` - Kilometers
- `mi` - Miles  
- `m` - Meters
- `ft` - Feet

**Use Cases:**
- Restaurant finder
- Store locator
- Real estate search
- Event proximity
- Service area coverage

---

### `geoBoundingBox($field, $bounds)`

Find documents within a rectangular area.

```php
// Search within bounding box
$query = new ElasticQuery();
$result = $query
    ->search('apartment', ['title^3', 'description'])
    ->geoBoundingBox('location', [
        'top_left' => '40.8,-74.1',
        'bottom_right' => '40.7,-73.9'
    ])
    ->filter('status', 'available')
    ->build();

// Alternative bounding box format
$query2 = new ElasticQuery();
result2 = $query2
    ->geoBoundingBox('location', [
        'top_left' => ['lat' => 40.8, 'lon' => -74.1],
        'bottom_right' => ['lat' => 40.7, 'lon' => -73.9]
    ])
    ->build();
```

**Use Cases:**
- Map viewport searches
- Regional filtering
- Administrative boundaries
- Coverage areas
- Delivery zones

---

### Geographic Sorting

Sort results by distance from a point.

```php
// Sort by distance
$query = new ElasticQuery();
$result = $query
    ->search('coffee shop', ['name^3', 'type'])
    ->geoDistance('location', '40.7128,-74.0060', '10km')
    ->sort([
        '_geo_distance' => [
            'location' => '40.7128,-74.0060',
            'order' => 'asc',
            'unit' => 'km'
        ]
    ])
    ->build();
```

---

## 🔍 Fuzzy & Advanced Search

Advanced search methods for handling typos, patterns, and flexible matching.

### `fuzzy($field, $value, $fuzziness = 'AUTO')`

Handle typos and similar spellings.

```php
// Handle spelling mistakes
$query = new ElasticQuery();
$result = $query
    ->fuzzy('title', 'javscript', 2)         // Finds "javascript" with 2 char difference
    ->fuzzy('author', 'jhon', 'AUTO')        // Finds "john" with auto fuzziness
    ->build();

// E-commerce search with fuzzy matching
$productQuery = new ElasticQuery();
$result = $productQuery
    ->fuzzy('name', 'iphone', 1)             // Handles "iphone" vs "iPhone"
    ->fuzzy('brand', 'samung', 'AUTO')       // Finds "samsung"
    ->filter('status', 'active')
    ->build();
```

**Fuzziness Options:**
- `0` - No fuzziness (exact match)
- `1` - 1 character difference allowed
- `2` - 2 character differences allowed
- `'AUTO'` - Automatic fuzziness based on term length

**Use Cases:**
- User typo tolerance
- Name variations
- Product search
- International spellings
- OCR text errors

---

### `wildcard($field, $pattern)`

Pattern matching with wildcards.

```php
// Wildcard searches
$query = new ElasticQuery();
$result = $query
    ->wildcard('filename', '*.pdf')          // All PDF files
    ->wildcard('email', '*@gmail.com')       // All Gmail addresses
    ->wildcard('product_code', 'LAP*')       // All laptop codes
    ->build();

// Log file analysis
$logQuery = new ElasticQuery();
result = $logQuery
    ->wildcard('path', '/api/v*/users/*')    // API paths with version
    ->wildcard('user_agent', '*mobile*')     // Mobile user agents
    ->build();
```

**Wildcard Characters:**
- `*` - Matches any number of characters
- `?` - Matches single character

**Performance Note:** Avoid leading wildcards (`*term`) as they can be slow.

---

### `regexp($field, $pattern)`

Regular expression matching for complex patterns.

```php
// Regular expression searches
$query = new ElasticQuery();
$result = $query
    ->regexp('phone', '[0-9]{3}-[0-9]{3}-[0-9]{4}')     // Phone format: 123-456-7890
    ->regexp('email', '[a-zA-Z0-9]+@[a-zA-Z0-9]+\\.[a-zA-Z]{2,}')  // Email pattern
    ->regexp('product_id', 'PRD[0-9]{6}')               // Product ID format
    ->build();

// IP address validation
$securityQuery = new ElasticQuery();
result = $securityQuery
    ->regexp('ip_address', '[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}')
    ->range('timestamp', 'gte', 'now-1h')
    ->build();
```

**Use Cases:**
- Format validation
- Pattern extraction
- Security analysis
- Data cleaning
- Complex text matching

---

### `nested($path, $callback)`

Search within nested objects.

```php
// Nested object search
$query = new ElasticQuery();
$result = $query
    ->search('programming course', ['title^3', 'description'])
    ->nested('comments', function($nestedQuery) {
        $nestedQuery
            ->match('comments.text', 'excellent')
            ->range('comments.rating', 'gte', 4)
            ->term('comments.verified', true);
    })
    ->build();

// E-commerce with nested attributes
$productQuery = new ElasticQuery();
result = $productQuery
    ->search('laptop', ['name^3', 'description'])
    ->nested('attributes', function($nestedQuery) {
        $nestedQuery
            ->term('attributes.name', 'RAM')
            ->range('attributes.value', 'gte', 16);
    })
    ->nested('reviews', function($nestedQuery) {
        $nestedQuery
            ->range('reviews.rating', 'gte', 4)
            ->range('reviews.created_at', 'gte', 'now-30d');
    })
    ->build();
```

**Use Cases:**
- Product attributes
- Comment systems
- Nested categories
- Multi-dimensional data
- Related objects

---

## 📊 Aggregation Methods

Powerful aggregation methods for analytics, facets, and data analysis.

### Basic Aggregations

### `aggregation($name, $config)`

Custom aggregation with full Elasticsearch DSL support.

```php
// Terms aggregation (categories/facets)
$query = new ElasticQuery();
result = $query
    ->search('tutorial', ['title^2', 'content'])
    ->filter('status', 'published')
    ->aggregation('categories', [
        'terms' => [
            'field' => 'category.keyword',
            'size' => 10
        ]
    ])
    ->build();

// Date histogram for time-series analysis
$analyticsQuery = new ElasticQuery();
result = $analyticsQuery
    ->range('timestamp', 'gte', 'now-30d')
    ->aggregation('daily_posts', [
        'date_histogram' => [
            'field' => 'created_at',
            'calendar_interval' => 'day',
            'format' => 'yyyy-MM-dd'
        ]
    ])
    ->build();

// Statistical aggregations
$salesQuery = new ElasticQuery();
result = $salesQuery
    ->range('order_date', 'gte', 'now-1M')
    ->aggregation('price_stats', [
        'stats' => [
            'field' => 'price'
        ]
    ])
    ->aggregation('revenue_sum', [
        'sum' => [
            'field' => 'total_amount'
        ]
    ])
    ->build();
```

---

### Laravel-Style Aggregation Methods

Familiar Laravel syntax for common aggregations.

### `count()`

Count total documents (sets size to 0).

```php
// Count documents
$query = new ElasticQuery();
result = $query
    ->filter('status', 'published')
    ->filter('category', 'tutorial')
    ->count()
    ->build();

// Result includes aggregation: {"total_count": {"value_count": {"field": "_id"}}}
```

---

### `avg($field)`

Calculate average value.

```php
// Average rating
$query = new ElasticQuery();
result = $query
    ->filter('status', 'published')
    ->avg('rating')
    ->build();

// Average price by category
$categoryQuery = new ElasticQuery();
result = $categoryQuery
    ->aggregation('categories', [
        'terms' => ['field' => 'category.keyword'],
        'aggs' => [
            'avg_price' => ['avg' => ['field' => 'price']]
        ]
    ])
    ->build();
```

---

### `sum($field)`

Calculate sum of values.

```php
// Total revenue
$query = new ElasticQuery();
result = $query
    ->range('order_date', 'gte', 'now-1M')
    ->sum('total_amount')
    ->build();
```

---

### `max($field)` and `min($field)`

Find maximum and minimum values.

```php
// Price range analysis
$query = new ElasticQuery();
result = $query
    ->filter('category', 'electronics')
    ->max('price')
    ->min('price')
    ->build();
```

---

### Advanced Aggregations

### Nested Aggregations

```php
// Complex nested aggregations
$query = new ElasticQuery();
result = $query
    ->aggregation('categories', [
        'terms' => [
            'field' => 'category.keyword',
            'size' => 10
        ],
        'aggs' => [
            'monthly_trends' => [
                'date_histogram' => [
                    'field' => 'created_at',
                    'calendar_interval' => 'month'
                ],
                'aggs' => [
                    'avg_rating' => ['avg' => ['field' => 'rating']],
                    'total_views' => ['sum' => ['field' => 'views']]
                ]
            ],
            'price_ranges' => [
                'range' => [
                    'field' => 'price',
                    'ranges' => [
                        ['to' => 50],
                        ['from' => 50, 'to' => 100],
                        ['from' => 100, 'to' => 200],
                        ['from' => 200]
                    ]
                ]
            ]
        ]
    ])
    ->build();
```

---

### E-commerce Aggregations Example

```php
// Complete e-commerce faceted search
$productQuery = new ElasticQuery();
result = $productQuery
    ->search('laptop', ['name^3', 'description'])
    ->filter('status', 'active')
    ->filter('in_stock', true)
    
    // Brand facets
    ->aggregation('brands', [
        'terms' => [
            'field' => 'brand.keyword',
            'size' => 20
        ]
    ])
    
    // Price ranges
    ->aggregation('price_ranges', [
        'range' => [
            'field' => 'price',
            'ranges' => [
                ['key' => 'budget', 'to' => 500],
                ['key' => 'mid_range', 'from' => 500, 'to' => 1500],
                ['key' => 'premium', 'from' => 1500]
            ]
        ]
    ])
    
    // Rating distribution
    ->aggregation('ratings', [
        'histogram' => [
            'field' => 'rating',
            'interval' => 1,
            'min_doc_count' => 1
        ]
    ])
    
    // Features
    ->aggregation('features', [
        'terms' => [
            'field' => 'features.keyword',
            'size' => 15
        ]
    ])
    
    ->build();
```

---

## 🔧 Sorting & Pagination

Methods for ordering results and implementing pagination.

### Sorting Methods

### `sort($field, $direction = 'asc')`

Basic field sorting.

```php
// Simple sorting
$query = new ElasticQuery();
result = $query
    ->search('tutorial', ['title^2', 'content'])
    ->sort('created_at', 'desc')     // Newest first
    ->sort('rating', 'desc')         // Then by rating
    ->build();

// Multi-field sorting array
$complexQuery = new ElasticQuery();
result = $complexQuery
    ->search('product', ['name^3', 'description'])
    ->sort([
        '_score' => 'desc',          // Relevance first
        'popularity' => 'desc',      // Then popularity
        'price' => 'asc'             // Then price low to high
    ])
    ->build();
```

---

### Laravel-Style Sorting

### `orderBy($field, $direction = 'asc')`

```php
// Laravel-style sorting
$query = new ElasticQuery();
result = $query
    ->search('blog post', ['title^2', 'content'])
    ->orderBy('published_at', 'desc')
    ->orderBy('views', 'desc')
    ->build();
```

---

### `orderByDesc($field)` and `latest($field)`

```php
// Descending shortcuts
$query = new ElasticQuery();
result = $query
    ->search('news', ['title^3', 'content'])
    ->orderByDesc('published_at')    // Same as orderBy('published_at', 'desc')
    ->build();

// Latest shortcut (defaults to 'created_at')
$latestQuery = new ElasticQuery();
result = $latestQuery
    ->filter('status', 'published')
    ->latest()                       // orderByDesc('created_at')
    ->build();

// Latest with custom field
$updatedQuery = new ElasticQuery();
result = $updatedQuery
    ->latest('updated_at')           // orderByDesc('updated_at')
    ->build();
```

---

### `oldest($field = 'created_at')`

```php
// Oldest first
$query = new ElasticQuery();
result = $query
    ->filter('status', 'archived')
    ->oldest()                       // orderBy('created_at', 'asc')
    ->build();
```

---

### Pagination Methods

### `size($count)` and `from($offset)`

```php
// Manual pagination
$query = new ElasticQuery();
result = $query
    ->search('tutorial', ['title^2', 'content'])
    ->filter('status', 'published')
    ->size(20)                       // 20 results per page
    ->from(40)                       // Skip first 40 (page 3)
    ->build();
```

---

### `paginate($perPage = 15, $page = 1)`

```php
// Automatic pagination calculation
$query = new ElasticQuery();
result = $query
    ->search('product', ['name^3', 'description'])
    ->filter('status', 'active')
    ->paginate(25, 3)                // 25 per page, page 3
    ->build();

// Default pagination (15 per page, page 1)
$defaultQuery = new ElasticQuery();
result = $defaultQuery
    ->search('article', ['title^2', 'content'])
    ->paginate()
    ->build();
```

---

### Laravel-Style Pagination Aliases

```php
// Laravel-familiar aliases
$query = new ElasticQuery();
result = $query
    ->search('item', ['name', 'description'])
    ->limit(10)                      // Alias for size(10)
    ->offset(20)                     // Alias for from(20)
    ->build();

// Alternative aliases
$query2 = new ElasticQuery();
result2 = $query2
    ->search('content', ['title', 'body'])
    ->take(15)                       // Alias for size(15)
    ->skip(30)                       // Alias for from(30)
    ->build();
```

---

## ⚙️ Utility Methods

Utility methods for response control, performance, and query optimization.

### Response Control

### `source($fields)`

Control which fields are returned in the response.

```php
// Specific fields only
$query = new ElasticQuery();
result = $query
    ->search('article', ['title^2', 'content'])
    ->filter('status', 'published')
    ->source(['id', 'title', 'summary', 'author', 'created_at'])
    ->build();

// Exclude source completely (IDs only)
$idsQuery = new ElasticQuery();
result = $idsQuery
    ->filter('category', 'news')
    ->source(false)
    ->build();

// Performance tip: Limiting source fields reduces response size and improves performance
```

---

### `highlight($fields)`

Add highlighting to search results.

```php
// Highlight search terms
$query = new ElasticQuery();
result = $query
    ->search('Laravel tutorial', ['title^2', 'content'])
    ->filter('status', 'published')
    ->highlight(['title', 'content'])
    ->build();

// Custom highlighting with options
$customQuery = new ElasticQuery();
result = $customQuery
    ->search('PHP development', ['title^3', 'content', 'tags'])
    ->highlight(['title', 'content'], [
        'fragment_size' => 150,
        'number_of_fragments' => 3,
        'pre_tags' => ['<mark>'],
        'post_tags' => ['</mark>']
    ])
    ->build();
```

---

### Performance Methods

### `timeout($timeout)`

Set query timeout to prevent long-running queries.

```php
// Set timeout
$query = new ElasticQuery();
result = $query
    ->search('complex query', ['title', 'content', 'tags'])
    ->aggregation('complex_agg', [/* complex aggregation */])
    ->timeout('5s')                  // 5 second timeout
    ->build();

// Different timeout formats
$timeoutQuery = new ElasticQuery();
result = $timeoutQuery
    ->search('data', ['field1', 'field2'])
    ->timeout('30s')                 // 30 seconds
    ->timeout('2m')                  // 2 minutes
    ->timeout('1h')                  // 1 hour
    ->build();
```

---

### `terminateAfter($count)`

Limit the number of documents examined.

```php
// Terminate after examining 10,000 documents
$query = new ElasticQuery();
result = $query
    ->search('sample', ['title', 'content'])
    ->terminateAfter(10000)
    ->build();
```

---

### Boolean Query Control

### `minimumShouldMatch($minimum)`

Control how many 'should' clauses must match.

```php
// At least 2 should clauses must match
$query = new ElasticQuery();
result = $query
    ->search('tutorial', ['title^2', 'content'])
    ->should(ElasticQuery::termClause('difficulty', 'beginner'))
    ->should(ElasticQuery::termClause('featured', true))
    ->should(ElasticQuery::matchClause('tags', 'popular'))
    ->minimumShouldMatch(2)          // At least 2 must match
    ->build();

// Percentage-based minimum
$percentQuery = new ElasticQuery();
result = $percentQuery
    ->should(ElasticQuery::matchClause('field1', 'value1'))
    ->should(ElasticQuery::matchClause('field2', 'value2'))
    ->should(ElasticQuery::matchClause('field3', 'value3'))
    ->minimumShouldMatch('75%')      // 75% of should clauses must match
    ->build();
```

---

### Advanced Utilities

### `scriptScore($script)`

Custom scoring with scripts.

```php
// Custom scoring based on popularity and recency
$query = new ElasticQuery();
result = $query
    ->search('article', ['title^2', 'content'])
    ->filter('status', 'published')
    ->scriptScore('Math.log(2 + doc["views"].value) + Math.log(2 + doc["shares"].value)')
    ->build();

// Boost recent documents
$recentBoostQuery = new ElasticQuery();
result = $recentBoostQuery
    ->search('news', ['title^3', 'content'])
    ->scriptScore('_score * Math.exp(-3.0 * Math.max(0, (System.currentTimeMillis() - doc["timestamp"].value) / 8.64e7))')
    ->build();
```

---

### Conditional Query Building

### `when($condition, $callback)`

Conditionally apply query modifications.

```php
// Conditional filtering
$userRole = 'subscriber'; // Could be 'admin', 'editor', 'subscriber'
$showDrafts = ($userRole === 'admin');

$query = new ElasticQuery();
result = $query
    ->search('article', ['title^2', 'content'])
    ->when($showDrafts, function($q) {
        return $q->whereIn('status', ['published', 'draft']);
    })
    ->when(!$showDrafts, function($q) {
        return $q->where('status', 'published');
    })
    ->when($userRole === 'admin', function($q) {
        return $q->source(['*']);  // All fields for admin
    })
    ->when($userRole !== 'admin', function($q) {
        return $q->source(['id', 'title', 'summary', 'created_at']);  // Limited fields
    })
    ->build();
```

---

### `unless($condition, $callback)`

Inverse conditional - apply when condition is false.

```php
// Apply filter unless user is admin
$isAdmin = false;

$query = new ElasticQuery();
result = $query
    ->search('document', ['title', 'content'])
    ->unless($isAdmin, function($q) {
        return $q->filter('public', true);  // Non-admins only see public docs
    })
    ->build();
```

---

## 🔧 Developer Experience

Powerful debugging and optimization tools to improve your development workflow.

### Verbose Mode

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

---

### Performance Warnings

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

**Warning Types:**

| Warning Type | Trigger | Recommendation |
|-------------|---------|----------------|
| **Wildcard** | `*` at start/end | Use filters or match queries instead |
| **Large Results** | `size > 10000` | Use pagination or scroll API |
| **Deep Pagination** | `from > 5000` | Use search_after for deep pagination |
| **Regex Queries** | `regexp()` method | Use wildcard or match for better performance |

---

### Error Translation

Convert cryptic Elasticsearch errors into human-readable messages:

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

**Example Translations:**
```
Original: "parsing_exception: [match] query does not support [invalid_parameter]"
Translated: "There's a syntax error in your query structure. Check the field names and query format."

Original: "search_phase_execution_exception timeout"
Translated: "Search operation timed out. Try simplifying your query or increase the timeout setting."

Original: "index_not_found_exception: no such index [products]"
Translated: "The index 'products' doesn't exist. Make sure the index name is correct and has been created."
```

---

### Developer Experience Methods

| Method | Description | Returns |
|--------|-------------|---------|
| `verbose()` | Enable verbose logging mode | `ElasticQuery` |
| `getVerboseLog()` | Get array of verbose log messages | `array<string>` |
| `getWarnings()` | Get all warnings (general + performance) | `array<string>` |
| `getPerformanceWarnings()` | Get performance-specific warnings | `array<string>` |
| `translateError($error)` | Translate error to human-readable format | `string` |

---

## 🛡️ Validation & Error Handling

QueryCraft includes comprehensive validation and error handling capabilities.

### Input Validation

Automatic validation of parameters:

```php
// These will throw InvalidArgumentException if invalid
$query = new ElasticQuery();

// Validates field names
$query->match('', 'value');           // ❌ Empty field name
$query->match('valid_field', 'value'); // ✅ Valid

// Validates range values
$query->range('price', 'invalid_operator', 100); // ❌ Invalid operator
$query->range('price', 'gte', 100);              // ✅ Valid

// Validates array parameters
$query->whereIn('status', 'not_array');          // ❌ Not an array
$query->whereIn('status', ['active', 'draft']);  // ✅ Valid array

// Validates geo coordinates
$query->geoDistance('location', 'invalid', '5km'); // ❌ Invalid coordinates
$query->geoDistance('location', '40.7,-74.0', '5km'); // ✅ Valid coordinates
```

---

### Exception Handling

Proper exception handling with detailed error messages:

```php
use Arthur2weber\QueryCraft\ElasticQuery;
use Arthur2weber\QueryCraft\Exceptions\InvalidArgumentException;
use Arthur2weber\QueryCraft\Exceptions\QueryBuilderException;

try {
    $query = new ElasticQuery();
    $result = $query
        ->match('title', 'Laravel')
        ->range('price', 'invalid_operator', 100) // Will throw exception
        ->build();
        
} catch (InvalidArgumentException $e) {
    echo "Parameter validation error: " . $e->getMessage();
    // Example: "Invalid range operator 'invalid_operator'. Allowed: gte, gt, lte, lt, eq"
    
} catch (QueryBuilderException $e) {
    echo "Query building error: " . $e->getMessage();
    
} catch (Exception $e) {
    echo "General error: " . $e->getMessage();
}
```

---

### Best Practices for Error Handling

```php
function searchProducts($searchTerm, $filters = []) {
    $query = new ElasticQuery();
    
    try {
        // Enable verbose mode in development
        if (app()->environment('local')) {
            $query->verbose();
        }
        
        // Build query with validation
        $result = $query
            ->match('name', $searchTerm)
            ->filter('status', 'active');
            
        // Apply filters with validation
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $result->whereIn($field, $value);
            } else {
                $result->filter($field, $value);
            }
        }
        
        $queryArray = $result->build();
        
        // Check for performance warnings
        $warnings = $query->getPerformanceWarnings();
        if (!empty($warnings)) {
            logger()->warning('Query performance issues detected', [
                'warnings' => $warnings,
                'query' => $queryArray
            ]);
        }
        
        return $queryArray;
        
    } catch (InvalidArgumentException $e) {
        // Handle validation errors
        logger()->error('Query validation failed', [
            'error' => $e->getMessage(),
            'search_term' => $searchTerm,
            'filters' => $filters
        ]);
        
        throw new Exception('Invalid search parameters: ' . $e->getMessage());
        
    } catch (Exception $e) {
        // Handle other errors with translation
        $humanError = $query->translateError($e->getMessage());
        logger()->error('Search query failed', [
            'original_error' => $e->getMessage(),
            'translated_error' => $humanError
        ]);
        
        throw new Exception($humanError);
    }
}
```

---

## 🚀 Real-World Examples

Comprehensive examples covering common use cases and patterns.

### E-commerce Product Search

Complete product search with faceted filtering:

```php
function searchProducts($searchTerm, $filters = [], $page = 1, $perPage = 20) {
    $query = new ElasticQuery();
    
    $result = $query
        // Main search across product fields
        ->search($searchTerm, [
            'name^5',           // Product name most important
            'brand^3',          // Brand quite important
            'description^1',    // Description normal weight
            'features^2'        // Features moderately important
        ])
        
        // Base filters
        ->filter('status', 'active')
        ->filter('availability', 'in_stock')
        
        // Apply dynamic filters
        ->when(isset($filters['category']), function($q) use ($filters) {
            return $q->filter('category', $filters['category']);
        })
        ->when(isset($filters['brand']), function($q) use ($filters) {
            return $q->whereIn('brand', $filters['brand']);
        })
        ->when(isset($filters['price_min']) || isset($filters['price_max']), function($q) use ($filters) {
            $priceFilter = [];
            if (isset($filters['price_min'])) $priceFilter['gte'] = $filters['price_min'];
            if (isset($filters['price_max'])) $priceFilter['lte'] = $filters['price_max'];
            return $q->range('price', $priceFilter);
        })
        
        // Faceted aggregations for filters
        ->termsAggregation('categories', 'category', 10)
        ->termsAggregation('brands', 'brand', 20)
        ->rangeAggregation('price_ranges', 'price', [
            ['key' => 'under_50', 'to' => 50],
            ['key' => '50_to_100', 'from' => 50, 'to' => 100],
            ['key' => '100_to_200', 'from' => 100, 'to' => 200],
            ['key' => 'over_200', 'from' => 200]
        ])
        
        // Sorting and pagination
        ->sort('_score', 'desc')        // Relevance first
        ->sort('popularity', 'desc')    // Then popularity
        ->sort('created_at', 'desc')    // Then recency
        ->from(($page - 1) * $perPage)
        ->size($perPage)
        
        // Limit response size
        ->source(['id', 'name', 'brand', 'price', 'image_url', 'rating'])
        
        ->build();
    
    return $result;
}

// Usage
$results = searchProducts('wireless headphones', [
    'category' => 'electronics',
    'brand' => ['Sony', 'Bose', 'Sennheiser'],
    'price_min' => 50,
    'price_max' => 300
]);
```

---

### Blog Content Management System

Content search with user permissions and advanced filtering:

```php
function searchArticles($searchTerm, $userRole = 'guest', $options = []) {
    $query = new ElasticQuery();
    
    $result = $query
        // Main content search
        ->search($searchTerm, [
            'title^4',          // Title most important
            'excerpt^3',        // Excerpt quite important
            'content^1',        // Content normal weight
            'tags^2'           // Tags moderately important
        ])
        
        // Base visibility rules
        ->filter('status', 'published')
        ->unless($userRole === 'admin', function($q) {
            return $q->filter('visibility', 'public');
        })
        
        // Role-based content access
        ->when($userRole === 'subscriber', function($q) {
            return $q->should([
                ['term' => ['access_level' => 'free']],
                ['term' => ['access_level' => 'subscriber']]
            ]);
        })
        ->when($userRole === 'premium', function($q) {
            return $q->whereIn('access_level', ['free', 'subscriber', 'premium']);
        })
        
        // Optional filters
        ->when(isset($options['category']), function($q) use ($options) {
            return $q->filter('category', $options['category']);
        })
        ->when(isset($options['author']), function($q) use ($options) {
            return $q->filter('author.keyword', $options['author']);
        })
        ->when(isset($options['date_from']), function($q) use ($options) {
            return $q->range('published_at', 'gte', $options['date_from']);
        })
        ->when(isset($options['featured']), function($q) {
            return $q->filter('featured', true);
        })
        
        // Content aggregations
        ->termsAggregation('categories', 'category', 10)
        ->termsAggregation('authors', 'author.keyword', 10)
        ->termsAggregation('tags', 'tags', 20)
        ->dateHistogramAggregation('monthly_posts', 'published_at', 'month')
        
        // Boost recent and popular content
        ->functionScore([
            'functions' => [
                [
                    'filter' => ['range' => ['published_at' => ['gte' => 'now-30d']]],
                    'weight' => 1.5  // Boost recent content
                ],
                [
                    'field_value_factor' => [
                        'field' => 'view_count',
                        'factor' => 0.1,
                        'modifier' => 'log1p'
                    ]
                ]
            ],
            'score_mode' => 'multiply',
            'boost_mode' => 'multiply'
        ])
        
        // Sorting and pagination
        ->sort('_score', 'desc')
        ->sort('published_at', 'desc')
        ->from($options['from'] ?? 0)
        ->size($options['size'] ?? 10)
        
        // Optimize response
        ->source([
            'id', 'title', 'excerpt', 'author', 'category', 
            'published_at', 'featured_image', 'reading_time', 'tags'
        ])
        
        ->build();
    
    return $result;
}

// Usage examples
$guestResults = searchArticles('Laravel tutorial', 'guest');

$memberResults = searchArticles('Advanced PHP', 'subscriber', [
    'category' => 'programming',
    'date_from' => '2024-01-01',
    'featured' => true
]);
```

---

### Geographic Location Search

Restaurant finder with location-based search and filtering:

```php
function findNearbyRestaurants($userLocation, $searchTerm = '', $options = []) {
    $query = new ElasticQuery();
    
    // Extract coordinates
    [$lat, $lon] = explode(',', $userLocation);
    $distance = $options['distance'] ?? '5km';
    
    $result = $query
        // Text search if provided
        ->when(!empty($searchTerm), function($q) use ($searchTerm) {
            return $q->search($searchTerm, [
                'name^4',
                'cuisine^3',
                'description^1',
                'specialties^2'
            ]);
        })
        
        // Geographic filtering
        ->geoDistance('location', $userLocation, $distance)
        
        // Base filters
        ->filter('status', 'open')
        ->filter('verified', true)
        
        // Optional filters
        ->when(isset($options['cuisine']), function($q) use ($options) {
            return $q->whereIn('cuisine', $options['cuisine']);
        })
        ->when(isset($options['price_range']), function($q) use ($options) {
            return $q->filter('price_range', $options['price_range']);
        })
        ->when(isset($options['rating_min']), function($q) use ($options) {
            return $q->range('rating', 'gte', $options['rating_min']);
        })
        ->when(isset($options['delivery']), function($q) {
            return $q->filter('offers_delivery', true);
        })
        ->when(isset($options['open_now']), function($q) {
            return $q->script([
                'script' => [
                    'source' => "
                        def now = System.currentTimeMillis();
                        def dayOfWeek = (int)(now / 86400000) % 7;
                        def timeOfDay = (int)((now % 86400000) / 60000);
                        
                        if (params.hours[dayOfWeek] == null) return false;
                        def todayHours = params.hours[dayOfWeek];
                        return timeOfDay >= todayHours.open && timeOfDay <= todayHours.close;
                    ",
                    'params' => [
                        'hours' => 'business_hours'
                    ]
                ]
            ]);
        })
        
        // Location-based aggregations
        ->termsAggregation('cuisines', 'cuisine', 15)
        ->termsAggregation('price_ranges', 'price_range', 5)
        ->geoDistanceAggregation('distance_ranges', 'location', $userLocation, [
            ['to' => 1000],      // Within 1km
            ['from' => 1000, 'to' => 3000],  // 1-3km
            ['from' => 3000, 'to' => 5000],  // 3-5km
            ['from' => 5000]     // Over 5km
        ])
        
        // Sort by distance and rating
        ->sort([
            '_geo_distance' => [
                'location' => $userLocation,
                'order' => 'asc',
                'unit' => 'km'
            ]
        ])
        ->sort('rating', 'desc')
        ->sort('review_count', 'desc')
        
        // Pagination
        ->from($options['from'] ?? 0)
        ->size($options['size'] ?? 20)
        
        // Optimize response
        ->source([
            'id', 'name', 'cuisine', 'rating', 'review_count',
            'price_range', 'image_url', 'address', 'location',
            'offers_delivery', 'estimated_delivery_time'
        ])
        
        ->build();
    
    return $result;
}

// Usage
$restaurants = findNearbyRestaurants('40.7128,-74.0060', 'pizza', [
    'distance' => '3km',
    'cuisine' => ['italian', 'american'],
    'rating_min' => 4.0,
    'delivery' => true,
    'open_now' => true
]);
```

---

### Analytics Dashboard

Data aggregation for business intelligence dashboard:

```php
function generateSalesAnalytics($dateRange, $filters = []) {
    $query = new ElasticQuery();
    
    $result = $query
        // Date range filter
        ->range('order_date', 'gte', $dateRange['from'])
        ->range('order_date', 'lte', $dateRange['to'])
        
        // Base filters
        ->filter('status', 'completed')
        ->filter('payment_status', 'paid')
        
        // Optional filters
        ->when(isset($filters['region']), function($q) use ($filters) {
            return $q->whereIn('shipping_address.region', $filters['region']);
        })
        ->when(isset($filters['product_category']), function($q) use ($filters) {
            return $q->nestedQuery('items', [
                'term' => ['items.category' => $filters['product_category']]
            ]);
        })
        ->when(isset($filters['customer_segment']), function($q) use ($filters) {
            return $q->filter('customer.segment', $filters['customer_segment']);
        })
        
        // Revenue metrics
        ->sumAggregation('total_revenue', 'total_amount')
        ->avgAggregation('average_order_value', 'total_amount')
        ->cardinalityAggregation('unique_customers', 'customer_id')
        
        // Time-based analysis
        ->dateHistogramAggregation('daily_sales', 'order_date', 'day', [
            'aggs' => [
                'revenue' => ['sum' => ['field' => 'total_amount']],
                'orders' => ['value_count' => ['field' => 'order_id']]
            ]
        ])
        
        // Geographic analysis
        ->termsAggregation('sales_by_region', 'shipping_address.region', 10, [
            'aggs' => [
                'revenue' => ['sum' => ['field' => 'total_amount']],
                'avg_order_value' => ['avg' => ['field' => 'total_amount']]
            ]
        ])
        
        // Product performance
        ->nestedAggregation('product_analysis', 'items', [
            'aggs' => [
                'top_products' => [
                    'terms' => ['field' => 'items.product_id', 'size' => 20],
                    'aggs' => [
                        'quantity_sold' => ['sum' => ['field' => 'items.quantity']],
                        'revenue' => ['sum' => ['field' => 'items.line_total']]
                    ]
                ],
                'categories' => [
                    'terms' => ['field' => 'items.category', 'size' => 10],
                    'aggs' => [
                        'revenue' => ['sum' => ['field' => 'items.line_total']],
                        'avg_price' => ['avg' => ['field' => 'items.unit_price']]
                    ]
                ]
            ]
        ])
        
        // Customer analysis
        ->termsAggregation('customer_segments', 'customer.segment', 5, [
            'aggs' => [
                'revenue' => ['sum' => ['field' => 'total_amount']],
                'order_frequency' => ['avg' => ['field' => 'customer.order_count']],
                'lifetime_value' => ['avg' => ['field' => 'customer.lifetime_value']]
            ]
        ])
        
        // Payment methods
        ->termsAggregation('payment_methods', 'payment_method', 10, [
            'aggs' => [
                'transaction_volume' => ['sum' => ['field' => 'total_amount']],
                'transaction_count' => ['value_count' => ['field' => 'order_id']]
            ]
        ])
        
        // Performance percentiles
        ->percentilesAggregation('order_value_percentiles', 'total_amount', [50, 75, 90, 95, 99])
        
        // Limit response (only aggregations matter for analytics)
        ->size(0)
        
        ->build();
    
    return $result;
}

// Usage
$analytics = generateSalesAnalytics([
    'from' => '2024-01-01',
    'to' => '2024-12-31'
], [
    'region' => ['north', 'south'],
    'customer_segment' => 'premium'
]);
```

---

## ⚡ Performance Best Practices

Guidelines and techniques for optimal query performance.

### Query Optimization Principles

#### 1. Use Filters Before Queries

Filters are cached and don't affect scoring, making them much faster:

```php
// ✅ Good - Filters first, then queries
$query = new ElasticQuery();
result = $query
    ->filter('status', 'published')        // Fast filter first
    ->filter('category', 'technology')     // Another fast filter
    ->search('Laravel PHP', ['title^2', 'content'])  // Then text search
    ->build();

// ❌ Slower - Queries first, then filters
$query = new ElasticQuery();
result = $query
    ->search('Laravel PHP', ['title^2', 'content'])  // Slow text search first
    ->filter('status', 'published')        // Filter after scoring
    ->filter('category', 'technology')
    ->build();
```

#### 2. Limit Source Fields

Only fetch the fields you need:

```php
// ✅ Good - Only fetch needed fields
$query = new ElasticQuery();
result = $query
    ->search('product search', ['name^3', 'description'])
    ->filter('status', 'active')
    ->source(['id', 'name', 'price', 'image_url'])  // Only what's needed
    ->size(20)
    ->build();

// ❌ Slower - Fetches all fields
$query = new ElasticQuery();
result = $query
    ->search('product search', ['name^3', 'description'])
    ->filter('status', 'active')
    // No source() call = fetches everything
    ->size(20)
    ->build();
```

#### 3. Use Appropriate Pagination

For deep pagination, use `search_after` instead of `from/size`:

```php
// ✅ Good - For shallow pagination (< 5000 results)
$query = new ElasticQuery();
result = $query
    ->search('articles', ['title^2', 'content'])
    ->filter('status', 'published')
    ->from(0)      // Page 1
    ->size(20)
    ->sort('created_at', 'desc')
    ->build();

// ✅ Better - For deep pagination (> 5000 results)
$query = new ElasticQuery();
result = $query
    ->search('articles', ['title^2', 'content'])
    ->filter('status', 'published')
    ->searchAfter([1234567890000, 'doc_id_123'])  // Use search_after
    ->size(20)
    ->sort(['created_at' => 'desc', '_id' => 'desc'])  // Tie-breaker sort
    ->build();
```

#### 4. Optimize Wildcard and Fuzzy Queries

Use them strategically to avoid performance issues:

```php
// ❌ Slow - Leading wildcard
$query = new ElasticQuery();
result = $query
    ->wildcard('title', '*script')  // Leading * is slow
    ->build();

// ✅ Better - Trailing wildcard
$query = new ElasticQuery();
result = $query
    ->wildcard('title', 'java*')    // Trailing * is faster
    ->build();

// ✅ Best - Use match with prefix or ngram
$query = new ElasticQuery();
result = $query
    ->match('title.prefix', 'java')  // Uses prefix analyzer
    ->build();

// Fuzzy optimization
// ❌ Too permissive
$query->fuzzy('title', 'javscript', 'AUTO');  // Could match anything

// ✅ More controlled
$query->fuzzy('title', 'javscript', 1);       // Max 1 edit distance
```

---

### Aggregation Performance

#### 1. Limit Aggregation Size

```php
// ✅ Good - Reasonable aggregation sizes
$query = new ElasticQuery();
result = $query
    ->filter('status', 'published')
    ->termsAggregation('categories', 'category', 10)      // Top 10 categories
    ->termsAggregation('authors', 'author.keyword', 5)    // Top 5 authors
    ->build();

// ❌ Potentially slow - Large aggregations
$query = new ElasticQuery();
result = $query
    ->termsAggregation('all_tags', 'tags', 10000)  // Too many terms
    ->build();
```

#### 2. Use Global Aggregations Wisely

```php
// ✅ Good - Global aggregations for facets
$query = new ElasticQuery();
result = $query
    ->search('laptop', ['name^3', 'description'])
    ->filter('category', 'electronics')
    
    // Regular aggregations (affected by filters)
    ->termsAggregation('brands', 'brand', 10)
    
    // Global aggregations (not affected by filters)
    ->globalAggregation('all_categories', [
        'terms' => ['field' => 'category', 'size' => 20]
    ])
    ->build();
```

---

### Memory and Resource Optimization

#### 1. Set Appropriate Timeouts

```php
// ✅ Good - Set reasonable timeouts
$query = new ElasticQuery();
result = $query
    ->search('complex search', ['title', 'content', 'tags'])
    ->timeout('5s')                    // 5 second timeout
    ->terminateAfter(10000)           // Stop after 10k docs processed
    ->build();
```

#### 2. Batch Operations Efficiently

```php
// ✅ Good - Process in batches
function searchInBatches($searchTerms, $batchSize = 100) {
    $results = [];
    $batches = array_chunk($searchTerms, $batchSize);
    
    foreach ($batches as $batch) {
        $query = new ElasticQuery();
        $result = $query
            ->whereIn('term', $batch)
            ->size($batchSize)
            ->source(['id', 'title'])  // Minimal fields
            ->build();
        
        $results[] = $result;
        
        // Small delay to prevent overwhelming ES
        usleep(10000); // 10ms delay
    }
    
    return $results;
}
```

---

### Index and Mapping Considerations

#### 1. Use Appropriate Field Types

```php
// ✅ Good - Use appropriate query types for field types
$query = new ElasticQuery();
result = $query
    // Text fields - use match/search
    ->match('description', 'search text')
    
    // Keyword fields - use term/filter
    ->filter('status.keyword', 'published')
    
    // Numeric fields - use range
    ->range('price', ['gte' => 10])
    
    // Date fields - use date math
    ->range('created_at', 'gte', 'now-7d')
    
    // Boolean fields - use term
    ->term('featured', true)
    ->build();
```

#### 2. Leverage Index Patterns

```php
// ✅ Good - Use time-based indices efficiently
$query = new ElasticQuery();

// Search only in recent indices for better performance
if ($dateRange['from'] >= 'now-30d') {
    $indices = ['logs-2024-*'];  // Only current month
} else {
    $indices = ['logs-*'];       // All indices
}

$result = $query
    ->range('@timestamp', 'gte', $dateRange['from'])
    ->range('@timestamp', 'lte', $dateRange['to'])
    ->build();

// Use with specific indices
// $response = $client->search(['index' => $indices, 'body' => $result]);
```

---

### Monitoring and Profiling

#### 1. Enable Performance Monitoring

```php
// ✅ Good - Monitor query performance
function monitoredSearch($query) {
    $startTime = microtime(true);
    
    // Enable verbose mode for debugging
    $query->verbose();
    
    $result = $query->build();
    
    $executionTime = microtime(true) - $startTime;
    
    // Log slow queries
    if ($executionTime > 0.1) { // 100ms threshold
        logger()->warning('Slow query detected', [
            'execution_time' => $executionTime,
            'query' => $result,
            'warnings' => $query->getPerformanceWarnings()
        ]);
    }
    
    return $result;
}
```

#### 2. Performance Testing

```php
// ✅ Good - Test query performance
function benchmarkQuery($query, $iterations = 10) {
    $times = [];
    
    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $result = $query->build();
        $times[] = microtime(true) - $start;
    }
    
    return [
        'avg_time' => array_sum($times) / count($times),
        'min_time' => min($times),
        'max_time' => max($times),
        'warnings' => $query->getPerformanceWarnings()
    ];
}

// Usage
$query = new ElasticQuery();
$query->search('test', ['title^2', 'content']);

$benchmark = benchmarkQuery($query);
echo "Average execution time: {$benchmark['avg_time']}ms\n";
```

---

### Performance Checklist

**Before deploying queries to production:**

- [ ] ✅ Filters are applied before text queries
- [ ] ✅ Source fields are limited to what's needed
- [ ] ✅ Appropriate pagination method is used
- [ ] ✅ Aggregation sizes are reasonable (< 1000 terms)
- [ ] ✅ Timeouts are set for complex queries
- [ ] ✅ Wildcard patterns avoid leading wildcards
- [ ] ✅ Fuzzy queries have controlled fuzziness
- [ ] ✅ Performance warnings are checked and addressed
- [ ] ✅ Queries are tested with realistic data volumes
- [ ] ✅ Monitoring is in place for slow queries

**QueryCraft specific optimizations:**

```php
// ✅ Complete performance-optimized query
$query = new ElasticQuery();
$query->verbose(); // Enable performance warnings

$result = $query
    // Filters first (fastest)
    ->filter('status', 'active')
    ->filter('category', 'electronics')
    ->range('price', ['gte' => 10, 'lte' => 1000])
    
    // Text search after filtering
    ->search('wireless headphones', ['name^4', 'brand^2', 'description'])
    
    // Controlled aggregations
    ->termsAggregation('brands', 'brand', 10)
    ->rangeAggregation('price_ranges', 'price', [
        ['key' => 'budget', 'to' => 100],
        ['key' => 'mid', 'from' => 100, 'to' => 300],
        ['key' => 'premium', 'from' => 300]
    ])
    
    // Performance optimizations
    ->source(['id', 'name', 'brand', 'price', 'image'])
    ->timeout('3s')
    ->size(20)
    ->from(0)  // Avoid deep pagination
    
    ->build();

// Check for warnings
$warnings = $query->getPerformanceWarnings();
if (!empty($warnings)) {
    foreach ($warnings as $warning) {
        echo "⚠️ Performance Warning: $warning\n";
    }
}
```

---

*🎉 **Wiki Complete!** This comprehensive guide covers all QueryCraft methods, patterns, and best practices. For the latest updates, visit the [GitHub repository](https://github.com/arthur2weber/query-craft).*
