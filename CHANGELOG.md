# Changelog

All notable changes to QueryCraft will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Comprehensive documentation reorganization
- Enhanced GitHub Copilot compatibility

## [1.0.0] - 2024-01-15

### Added
- Initial release of QueryCraft
- Laravel-inspired fluent syntax for Elasticsearch DSL
- 50+ query methods covering all major Elasticsearch features
- Developer experience features (verbose mode, error translation, performance warnings)
- Complete text search capabilities (match, search, phrase, fuzzy, etc.)
- Boolean logic support (must, should, mustNot, filter)
- Range and term queries with full operator support
- Geographic search (geoDistance, geoBoundingBox)
- Advanced aggregations support with nested capabilities
- Comprehensive test suite with 94.7% success rate
- Complete PHPDoc documentation
- PSR-4 autoloading compliance
- Zero external dependencies

### Features
- **Text Search**: match, search, matchPhrase, queryString, fuzzy, wildcard, regexp
- **Boolean Logic**: must, should, mustNot, filter with clause composition
- **Term Queries**: term, terms, range, exists, prefix with boost support
- **Geographic**: geoDistance, geoBoundingBox with distance sorting
- **Aggregations**: terms, date_histogram, stats, nested aggregations
- **Developer Experience**: verbose logging, performance warnings, error translation
- **Utilities**: sorting, pagination, source filtering, highlighting
- **Validation**: Parameter validation and query optimization

### Documentation
- Complete README with usage examples
- Quick reference guide with all methods
- Developer experience guide
- Contributing guidelines
- Security policy
- Testing documentation
- Comprehensive examples directory

### Testing
- 75+ unit tests across all functionality
- Ultra-fast testing scripts (./tt and php t.php)
- Category-based test organization
- Performance benchmarking
- Integration examples

## [0.9.0] - 2023-12-01

### Added
- Beta release for community feedback
- Core query building functionality
- Basic test suite

## [0.1.0] - 2023-11-01

### Added
- Initial proof of concept
- Basic ElasticQuery class structure
- Laravel-style method signatures

---

## Release Notes

### Version 1.0.0 Highlights

QueryCraft 1.0.0 represents a complete and production-ready Elasticsearch DSL query builder for PHP. This release focuses on:

- **Developer Productivity**: Laravel-familiar syntax reduces learning curve
- **Zero Dependencies**: Pure PHP implementation for maximum compatibility  
- **Complete Coverage**: All major Elasticsearch 7.x+ features supported
- **Developer Experience**: Advanced debugging and optimization tools
- **Documentation**: Comprehensive guides and practical examples
- **Testing**: Robust test suite ensuring reliability

### Upcoming Features (Roadmap)

See [ROADMAP.md](docs/ROADMAP.md) for detailed future plans including:
- Intelligent caching system
- Custom macro support  
- Query analytics and monitoring
- CLI tools for development
- Machine learning integration

---

**Note**: This project follows semantic versioning. Breaking changes will only be introduced in major version releases.
