<?php

namespace Tests\Unit\MongoQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class GeneratedSmokeTest extends TestCase
{
    public function testPublicMethodsDoNotThrowUnexpectedExceptions()
    {
        $instance = new MongoQuery();
        $ref = new \ReflectionClass($instance);
        $methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $declaring = $method->getDeclaringClass()->getName();
            if (!str_starts_with($declaring, 'Arthur2weber')) {
                continue;
            }
            if ($method->isConstructor()) {
                continue;
            }
            if (str_starts_with($method->getName(), '__')) {
                continue;
            }

            $args = [];
            foreach ($method->getParameters() as $param) {
                $args[] = $this->safeValueForParameter($param);
            }

            try {
                $result = $method->invokeArgs($instance, $args);

                // If the implementation returns null for some inputs, treat as acceptable
                if ($result === null) {
                    $this->addToAssertionCount(1);
                    continue;
                }

                $rtype = $method->getReturnType();
                if ($rtype instanceof \ReflectionNamedType) {
                    $name = $rtype->getName();
                    if ($name === 'array') {
                        $this->assertIsArray($result);
                    } elseif ($name === 'string') {
                        $this->assertIsString($result);
                    } elseif ($name === 'int') {
                        $this->assertIsInt($result);
                    }
                }
            } catch (\Throwable $e) {
                if ($e instanceof \InvalidArgumentException) {
                    $this->addToAssertionCount(1);
                    continue;
                }
                throw $e;
            }
        }
    }

    protected function safeValueForParameter(\ReflectionParameter $param)
    {
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        $name = $param->getName();
        $type = $param->getType();

        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $t) {
                if ($t instanceof \ReflectionNamedType && $t->getName() !== 'null') {
                    $type = $t;
                    break;
                }
            }
        }

        if ($type instanceof \ReflectionNamedType) {
            $tname = $type->getName();
            switch ($tname) {
                case 'string':
                    if (str_contains($name, 'field') || str_contains($name, 'index') || str_contains($name, 'collection') || str_contains($name, 'column')) {
                        return 'test_field';
                    }
                    if (str_contains($name, 'operator')) {
                        return '=';
                    }
                    if (str_contains($name, 'distance')) {
                        return '5km';
                    }
                    if (str_contains($name, 'lat') || str_contains($name, 'lon') || str_contains($name, 'location')) {
                        return '1,1';
                    }
                    if (str_contains($name, 'timeout')) {
                        return '30s';
                    }
                    if (str_contains($name, 'script')) {
                        return 'return 1;';
                    }
                    if (str_contains($name, 'pattern')) {
                        return 'pat*';
                    }
                    return 'value';
                case 'int':
                    return 1;
                case 'float':
                    return 1.0;
                case 'bool':
                    return true;
                case 'array':
                    if (str_contains($name, 'fields') || str_contains($name, 'select') || str_contains($name, 'values')) {
                        return ['field1'];
                    }
                    if (str_contains($name, 'range')) {
                        return [1, 2];
                    }
                    if (str_contains($name, 'aggregation') || str_contains($name, 'agg')) {
                        return ['terms' => ['field' => 'field1']];
                    }
                    if (str_contains($name, 'geometry')) {
                        return ['type' => 'Point', 'coordinates' => [0,0]];
                    }
                    return ['a'];
                case 'callable':
                    return function ($q = null) { return $q; };
                case 'mixed':
                    return 'value';
                default:
                    return 'value';
            }
        }

        if (str_contains($name, 'fields') || str_contains($name, 'select') || str_contains($name, 'values')) {
            return ['field1'];
        }
        if (str_contains($name, 'page') || str_contains($name, 'per')) {
            return 1;
        }
        if (str_contains($name, 'bool') || str_contains($name, 'enable')) {
            return true;
        }

        return 'value';
    }
}
