# Security Policy

## Supported Versions

We actively support the following versions of QueryCraft with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

We take security vulnerabilities seriously. If you discover a security vulnerability in QueryCraft, please follow these steps:

### 🔒 Private Disclosure

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please report security vulnerabilities by emailing:
- **Email**: arthur2weber@gmail.com
- **Subject**: `[Security] QueryCraft Vulnerability Report`

### 📝 Information to Include

When reporting a vulnerability, please include:

1. **Description**: A clear description of the vulnerability
2. **Impact**: Potential impact and attack scenarios
3. **Reproduction**: Step-by-step instructions to reproduce the issue
4. **Environment**: PHP version, Elasticsearch version, library version
5. **Code Sample**: Minimal code that demonstrates the vulnerability
6. **Suggested Fix**: If you have ideas for fixing the issue

### ⏱️ Response Timeline

- **Acknowledgment**: We will acknowledge your report within 48 hours
- **Initial Assessment**: We will provide an initial assessment within 5 business days
- **Resolution**: We aim to resolve critical vulnerabilities within 30 days
- **Disclosure**: We will coordinate disclosure timeline with you

### 🛡️ Security Best Practices

When using QueryCraft:

#### Input Validation
```php
// ✅ Good: Validate and sanitize user input
$userQuery = filter_var($userInput, FILTER_SANITIZE_STRING);
$query = new ElasticQuery();
$query->match('title', $userQuery);

// ❌ Bad: Direct user input without validation
$query->match('title', $_GET['search']); // Potentially unsafe
```

#### Query Size Limits
```php
// ✅ Good: Limit query size to prevent resource exhaustion
$maxSize = min((int)$userRequestedSize, 1000);
$query->size($maxSize);

// ❌ Bad: Unlimited query size
$query->size($_GET['size']); // Could cause memory issues
```

#### Field Access Control
```php
// ✅ Good: Restrict source fields to prevent data leakage
$allowedFields = ['title', 'summary', 'public_data'];
$query->source($allowedFields);

// ❌ Bad: Exposing all fields
$query->source(['*']); // Could expose sensitive data
```

#### Timeout Configuration
```php
// ✅ Good: Set reasonable timeouts
$query->timeout('5s');

// ❌ Bad: No timeout limits
// Could lead to resource exhaustion
```

### 🔍 Known Security Considerations

1. **Query Injection**: Always validate and sanitize user input before using it in queries
2. **Resource Exhaustion**: Implement proper size limits and timeouts
3. **Data Exposure**: Use source field filtering to prevent sensitive data leakage
4. **Access Control**: Implement proper authentication and authorization in your application layer

### 📚 Additional Resources

- [Elasticsearch Security Best Practices](https://www.elastic.co/guide/en/elasticsearch/reference/current/security-getting-started.html)
- [PHP Security Guidelines](https://www.php.net/manual/en/security.php)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)

### 🏆 Recognition

We appreciate security researchers who help keep our project safe. With your permission, we will:

- Credit you in our security advisories
- Include your name in our CHANGELOG for security fixes
- Provide you with early access to new security features

### 📞 Contact Information

For security-related questions or concerns:
- **Email**: [security@yourdomain.com]
- **Response Time**: Within 48 hours for security issues

For general questions, please use our regular support channels:
- **GitHub Issues**: For non-security bugs and feature requests
- **Discussions**: For questions and community support

---

**Last Updated**: August 2025
