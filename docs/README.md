# 📚 QueryCraft Documentation

Complete documentation for the QueryCraft Elasticsearch DSL Query Builder.

## 🚀 Getting Started

| Document | Description | Best For |
|----------|-------------|----------|
| **[📖 Complete Wiki](WIKI.md)** | **Comprehensive guide with all methods and examples** | **Complete reference** |
| **[Main README](../README.md)** | Installation, quick start, core features | New users, overview |
| **[Quick Reference](quick-reference.md)** | All methods with examples | Daily development |
| **[Developer Experience](developer-experience.md)** | Debugging, optimization, troubleshooting | Advanced usage |
|

## 📖 Detailed Guides

### Core Documentation
- **[Testing Guide](TESTING.md)** - How to run tests and contribute
- **[Contributing Guide](CONTRIBUTING.md)** - Contribution guidelines and workflow
- **[Security Policy](SECURITY.md)** - Security reporting and best practices

### Project Information  
- **[Roadmap](ROADMAP.md)** - Future features and development timeline
- **[Changelog](../CHANGELOG.md)** - Version history and release notes

## 🎯 Quick Navigation

### For Developers
- [Method Reference](quick-reference.md#query-methods) - Complete API reference
- [Examples Collection](../examples/) - Real-world usage patterns
- [Testing Commands](TESTING.md#execução-rápida-com-scripts-de-teste) - Ultra-fast testing

### For Contributors
- [Quick Start](CONTRIBUTING.md#quick-start-for-contributors) - Get started in 5 minutes
- [Development Process](CONTRIBUTING.md#development-process) - Workflow and standards
- [Future Plans](ROADMAP.md#improvement-proposals-by-category) - What's coming next

## 📁 Examples by Category

The [`examples/`](../examples/) directory contains practical usage examples:

| File | Focus | Use Case |
|------|-------|----------|
| `blog-search.php` | Content search | Blog, articles, CMS |
| `ecommerce-search.php` | Product search | E-commerce, catalogs |
| `geographic-search.php` | Location queries | Maps, local search |
| `analytics-aggregations.php` | Data analysis | Dashboards, reports |
| `developer-experience.php` | Debugging tools | Development, testing |

## 🔧 Development Tools

QueryCraft includes several tools to improve developer experience:

### 💎 Laravel Migration Guide
```php
// Step 1: Replace your Eloquent model
// Before: $posts = Post::where('status', 'published')
// After:  $posts = (new ElasticQuery())->where('status', 'published')

// Step 2: Add Elasticsearch features
$posts = (new ElasticQuery())
    ->search($term, ['title^3', 'content'])  // Full-text search
    ->where('status', 'published')           // Same Eloquent syntax
    ->orderByDesc('_score')                  // Sort by relevance
    ->paginate(15);                          // Same pagination
```

### Testing
```bash
./tt              # Quick tests
./tt all          # Complete test suite
./tt core         # Core functionality tests
```

### Examples
```bash
php examples/run-all.php    # Run all examples
```

### Debugging
```php
$query->verbose();          // Enable verbose logging
$query->getVerboseLog();    // Get detailed logs
$query->getWarnings();      # Get performance warnings
```

## 🤝 Community

- **Issues**: [Report bugs or request features](https://github.com/arthur2weber/query-craft/issues)
- **Discussions**: Ask questions and share ideas
- **Contributing**: See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines

---

**💡 Tip**: Start with the [Quick Reference](quick-reference.md) for daily development, then explore [Developer Experience](developer-experience.md) for advanced features.

## 🧪 **Testing Commands**

```bash
# Quick test (development)
php t.php

# All tests (before commits)
php t.php all

# Alternative methods
make test    # Via makefile
./tt         # Via bash script
```

## 📊 **Project Structure**

```
docs/
├── README.md              # This file - documentation index
├── CHANGELOG.md           # Version history
├── CONTRIBUTING.md        # Contribution guide
├── quick-reference.md     # API quick reference
├── ROADMAP.md            # Development roadmap
├── SECURITY.md           # Security policy
└── TESTING.md            # Testing guide
```

---

**🎯 Most Important**: Start with [../README.md](../README.md) for usage guide and [TESTING.md](TESTING.md) for running tests!
