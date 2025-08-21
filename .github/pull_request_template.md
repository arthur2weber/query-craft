# Pull Request Template

## Description
Brief description of the changes introduced by this PR.

## Related Issue
- Fixes #(issue number)
- Relates to #(issue number)

## Type of Change
Please delete options that are not relevant.

- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update
- [ ] Performance improvement
- [ ] Code refactoring
- [ ] Test improvement

## Changes Made
- [ ] Add new query method: `methodName()`
- [ ] Fix issue with aggregation handling
- [ ] Update documentation
- [ ] Add new examples
- [ ] Improve error handling

## Testing
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes
- [ ] I have tested this with real Elasticsearch queries

## Code Quality
- [ ] My code follows the style guidelines of this project
- [ ] I have performed a self-review of my own code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] My changes generate no new warnings

## Documentation
- [ ] I have updated the README.md if needed
- [ ] I have added/updated PHPDoc comments
- [ ] I have added examples demonstrating the new functionality
- [ ] I have updated the ROADMAP.md if this relates to a planned feature

## Breaking Changes
If this PR introduces breaking changes, please describe them here:

- Change 1: Description and migration path
- Change 2: Description and migration path

## Examples
If applicable, provide examples of how to use the new functionality:

```php
<?php
use Arthur2weber\QueryCraft\ElasticQuery;

$query = new ElasticQuery();
// Example usage of new feature
```

## Additional Notes
Add any other context about the pull request here.

## Checklist for Reviewers
- [ ] Code review completed
- [ ] Tests are passing
- [ ] Documentation is adequate
- [ ] Examples are clear and helpful
- [ ] Breaking changes are well documented
