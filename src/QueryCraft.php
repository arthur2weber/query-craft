<?php

namespace Arthur2weber\QueryCraft;

// Load the required classes
require_once __DIR__ . '/ElasticQueryInterface.php';
require_once __DIR__ . '/Query/BaseQuery.php';
require_once __DIR__ . '/Query/ElasticQuery.php';
require_once __DIR__ . '/Query/MongoQuery.php';
require_once __DIR__ . '/Query/GraphQuery.php';

use Arthur2weber\QueryCraft\Query\ElasticQuery;
use Arthur2weber\QueryCraft\Query\MongoQuery;
use Arthur2weber\QueryCraft\Query\GraphQuery;

/**
 * QueryCraft - Universal Query Builder Factory
 * 
 * Main factory class for creating specialized query builders for different backends.
 * Provides a unified interface to create ElasticQuery, MongoQuery, and GraphQuery instances
 * while maintaining backward compatibility with the existing API.
 * 
 * @package Arthur2weber\QueryCraft
 * @author Arthur Weber <arthur2weber@gmail.com>
 * @version 2.0.0
 * 
 * @example Elasticsearch query
 * $products = QueryCraft::elastic()
 *     ->from('products')
 *     ->where('status', 'active')
 *     ->search('wireless headphones', ['name^3', 'description'])
 *     ->paginate(15);
 * 
 * @example MongoDB query
 * $users = QueryCraft::mongo()
 *     ->from('users')
 *     ->where('active', true)
 *     ->lookup('profiles', '_id', 'user_id', 'profile')
 *     ->get();
 * 
 * @example GraphQL query
 * $posts = QueryCraft::graph()
 *     ->from('posts')
 *     ->where('published', true)
 *     ->with(['author', 'comments'])
 *     ->select(['id', 'title', 'content'])
 *     ->toQuery();
 */
class QueryCraft
{
    /**
     * Create an Elasticsearch query builder
     */
    public static function elastic(): ElasticQuery
    {
        return new ElasticQuery();
    }

    /**
     * Create a MongoDB query builder
     */
    public static function mongo(): MongoQuery
    {
        return new MongoQuery();
    }

    /**
     * Create a GraphQL query builder
     */
    public static function graph(): GraphQuery
    {
        return new GraphQuery();
    }

    /**
     * Legacy method for backward compatibility
     * 
     * @deprecated Use specific methods like elastic(), mongo(), graph() instead
     */
    public static function for(string $type, array $config = [])
    {
        return match ($type) {
            'elasticsearch', 'elastic' => self::elastic(),
            'mongodb', 'mongo' => self::mongo(),
            'graphql', 'graph' => self::graph(),
            default => throw new \InvalidArgumentException("Unsupported query type: {$type}. Supported types: elasticsearch, mongodb, graphql")
        };
    }

    /**
     * Get all available query builders
     */
    public static function available(): array
    {
        return [
            'elasticsearch' => ElasticQuery::class,
            'mongodb' => MongoQuery::class,
            'graphql' => GraphQuery::class
        ];
    }

    /**
     * Check if a query type is supported
     */
    public static function supports(string $type): bool
    {
        return array_key_exists($type, self::available());
    }

    /**
     * Get version information
     */
    public static function version(): string
    {
        return '2.0.0';
    }
}
