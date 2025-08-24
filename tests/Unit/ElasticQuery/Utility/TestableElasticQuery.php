<?php

namespace Tests\Unit\ElasticQuery\Utility;

use Arthur2weber\QueryCraft\Query\ElasticQuery as BaseElastic;

class TestableElasticQuery extends BaseElastic
{
    public function callValidateGeoCoordinates($lat, $lon)
    {
        return $this->validateGeoCoordinates($lat, $lon);
    }

    public function callValidateAggregation(string $name, array $agg)
    {
        return $this->validateAggregation($name, $agg);
    }

    public function callValidateScript(string $script)
    {
        return $this->validateScript($script);
    }

    public function callValidateConditionValues(array $cond, int $depth = 0, int $maxDepth = 50)
    {
        return $this->validateConditionValues($cond, $depth, $maxDepth);
    }

    public function callAreClausesConflicting(array $c1, array $c2, string $field, $value): bool
    {
        return $this->areClausesConflicting($c1, $c2, $field, $value);
    }

    public function exposeCache(): array
    {
        $ref = new \ReflectionClass($this);
        $prop = $ref->getProperty('clauseCache');
        $prop->setAccessible(true);
        return $prop->getValue();
    }

    public function exposeCacheSize(): int
    {
        $ref = new \ReflectionClass($this);
        $prop = $ref->getProperty('cacheSize');
        $prop->setAccessible(true);
        return $prop->getValue();
    }

    // Implement abstract stubs (some already implemented in BaseElastic but satisfy type hints)
    public function get(): array { return $this->build(); }
    public function first(): ?array { return parent::first(); }
    public function count(): int { return 0; }
    public function paginate(int $perPage = 15, int $page = 1): array { return parent::paginate($perPage, $page); }
    public function toQuery(): mixed { return $this->build(); }
}

// Bridge class for legacy references. Some tests instantiate \Tests\Unit\ElasticQuery\TestableElasticQuery
// (without the Utility subnamespace). Defining this lightweight subclass here ensures those references work
// when the Utility file is required, avoiding edits across many test files.

namespace Tests\Unit\ElasticQuery;

class TestableElasticQuery extends \Tests\Unit\ElasticQuery\Utility\TestableElasticQuery {}

// Provide aliases for common sub-namespaces that instantiate TestableElasticQuery
// unqualified (e.g. namespace Tests\Unit\ElasticQuery\GeoLocation). This avoids
// editing many test files and keeps backward-compatible FQCNs.

if (!\class_exists('\\Tests\\Unit\\ElasticQuery\\GeoLocation\\TestableElasticQuery')) {
    \class_alias('\\Tests\\Unit\\ElasticQuery\\TestableElasticQuery', '\\Tests\\Unit\\ElasticQuery\\GeoLocation\\TestableElasticQuery');
}
