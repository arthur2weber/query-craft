# Contributing to QueryCraft

We love your input! We want to make contributing to QueryCraft as easy and transparent as possible, whether it's:

- Reporting a bug
- Discussing the current state of the code
- Submitting a fix
- Proposing new features
- Becoming a maintainer

## 🚀 Quick Start for Contributors

1. **Fork the repository**
2. **Clone your fork**: `git clone https://github.com/yourusername/query-craft.git`
3. **Install dependencies**: `composer install`
4. **Create a branch**: `git checkout -b feature/amazing-feature`
5. **Make your changes**
6. **Test your changes**: `php examples/run-all.php`
7. **Commit**: `git commit -m 'Add amazing feature'`
8. **Push**: `git push origin feature/amazing-feature`
9. **Create a Pull Request**

## 🔧 Development Process

We use GitHub to host code, to track issues and feature requests, as well as accept pull requests.

### Code Changes Happen Through Pull Requests

Pull requests are the best way to propose changes to the codebase. We actively welcome your pull requests:

1. Fork the repo and create your branch from `main`
2. If you've added code that should be tested, add tests
3. If you've changed APIs, update the documentation
4. Ensure the test suite passes
5. Make sure your code lints
6. Issue that pull request!

## 📝 Coding Standards

### PHP Standards

We follow PSR-12 coding standards with some additional conventions:

```php
<?php
/**
 * Class documentation with clear description
 * 
 * @package Arthur2weber\QueryCraft
 */
class ExampleClass
{
    /**
     * Method documentation explaining purpose and usage
     * 
     * @param string $field The field name to search
     * @param mixed $value The value to search for
     * @return self Returns instance for method chaining
     * 
     * @example
     * $query = new ElasticQuery();
     * $query->match('title', 'search term');
     */
    public function match(string $field, $value): self
    {
        // Implementation
        return $this;
    }
}
```

### Documentation Standards

- **PHPDoc**: All public methods must have complete PHPDoc comments
- **Examples**: Include practical examples in method documentation
- **Type Hints**: Use strict typing where possible
- **Return Types**: Always specify return types

### Naming Conventions

- **Classes**: PascalCase (`ElasticQuery`)
- **Methods**: camelCase (`search`)
- **Variables**: camelCase (`$fieldName`)
- **Constants**: SCREAMING_SNAKE_CASE (`DEFAULT_SIZE`)

## 🧪 Testing

### Running Tests

Our **ultra-fast testing system** supports multiple execution methods:

```bash
# Quick test (development workflow)
php t.php           # 25 essential tests (~50ms)

# Complete test suite
php t.php all       # 261 tests (~297ms)

# Category-specific tests
php t.php core      # Core functionality
php t.php search    # Search features 
php t.php filters   # Filter features

# Alternative commands
make test           # Via makefile
./tt                # Bash script

# Integration tests
php examples/run-all.php
```

### Test Categories

- **Core**: Basic query building (BasicQueryTest, BooleanQueryTest, NestedQueryTest, StaticClauseTest)
- **Search**: Text search functionality (TextSearchTest, FuzzyWildcardTest)
- **Filters**: Filtering capabilities (WhereClauseTest, RangeTermTest)
- **Utilities**: Helper methods and utilities (UtilityMethodsTest - 41 tests covering pagination, output control, conditionals, scopes, validation, analysis, and caching)
- **Advanced**: Aggregations, geographic, sorting (AggregationTest, GeographicTest, SortingTest)
- **Validation**: Input validation and error handling (ComprehensiveValidationTest)

### Writing Tests

When adding new features, please include tests in the appropriate category:

```php
// tests/Unit/[Category]/YourFeatureTest.php
require_once __DIR__ . '/../../TestHelpers.php';
use Arthur2weber\QueryCraft\ElasticQuery;

echo "🧪 YOUR FEATURE: Description\n";
echo str_repeat("=", 40) . "\n\n";

resetTestCounters();

// Test new method
$query = new ElasticQuery();
$result = $query->newMethod('param')->build();
runTest("New method creates correct structure", isset($result['query']));

// Show results
$results = getTestResults();
echo "\n📊 {$results['passed']}/{$results['total']} tests passed ({$results['success_rate']}%)\n";
```

## 📚 Documentation

### Code Documentation

- Use clear, descriptive variable names
- Comment complex logic
- Include examples in PHPDoc comments
- Update README.md for new features

### Example Files

When adding new features, consider creating or updating example files:

- `examples/blog-search.php` - Blog-related searches
- `examples/ecommerce-search.php` - E-commerce examples
- `examples/geographic-search.php` - Location-based searches
- `examples/analytics-aggregations.php` - Analytics examples

## 🐛 Bug Reports

We use GitHub issues to track public bugs. Report a bug by [opening a new issue](https://github.com/arthur2weber/query-craft/issues/new/choose).

**Great Bug Reports** tend to have:

- A quick summary and/or background
- Steps to reproduce
  - Be specific!
  - Give sample code if you can
- What you expected would happen
- What actually happens
- Notes (possibly including why you think this might be happening, or stuff you tried that didn't work)

## 💡 Feature Requests

We welcome feature requests! Before submitting:

1. **Check existing issues** to avoid duplicates
2. **Consider the scope** - does it fit the library's purpose?
3. **Provide use cases** - explain how it would be used
4. **Consider implementation** - how might it work?

## 🏷️ Versioning

We use [SemVer](http://semver.org/) for versioning:

- **MAJOR**: Incompatible API changes
- **MINOR**: Backwards-compatible functionality additions
- **PATCH**: Backwards-compatible bug fixes

## 📋 Pull Request Process

### Before Submitting

- [ ] Code follows PSR-12 standards
- [ ] All tests pass
- [ ] Documentation is updated
- [ ] Examples demonstrate new features
- [ ] CHANGELOG.md is updated

### Pull Request Template

Your PR should include:

1. **Clear description** of changes
2. **Related issue** number (if applicable)
3. **Type of change** (bug fix, feature, breaking change)
4. **Testing performed**
5. **Documentation updates**

### Review Process

1. **Automated checks** must pass
2. **Code review** by maintainers
3. **Testing** in various environments
4. **Documentation review**
5. **Merge** when approved

## 🎯 Areas for Contribution

### High Priority

- **Performance optimizations**
- **Additional query types**
- **Better error handling**
- **More comprehensive tests**

### Medium Priority

- **Documentation improvements**
- **Example applications**
- **IDE integration helpers**
- **Debugging tools**

### Good First Issues

Look for issues labeled `good first issue` for beginner-friendly contributions:

- Documentation fixes
- Simple bug fixes
- Adding examples
- Improving error messages

## 🤝 Code of Conduct

### Our Pledge

We pledge to make participation in our project a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, nationality, personal appearance, race, religion, or sexual identity and orientation.

### Our Standards

**Positive behavior includes:**

- Using welcoming and inclusive language
- Being respectful of differing viewpoints
- Gracefully accepting constructive criticism
- Focusing on what is best for the community

**Unacceptable behavior includes:**

- Trolling, insulting/derogatory comments
- Public or private harassment
- Publishing others' private information
- Other conduct which could reasonably be considered inappropriate

## 📞 Contact

- **GitHub Issues**: For bugs and feature requests
- **Email**: [maintainer@email.com] (replace with actual email)
- **Discussions**: For questions and community support

## 📄 License

By contributing, you agree that your contributions will be licensed under the same MIT License that covers the project. Feel free to contact the maintainers if that's a concern.

## 🙏 Recognition

Contributors will be recognized in:

- CHANGELOG.md for their contributions
- README.md contributors section
- Release notes for significant contributions

Thank you for contributing to QueryCraft! 🚀