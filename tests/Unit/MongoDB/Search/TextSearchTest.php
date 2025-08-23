<?php
/**
 * MongoDB Text Search Test
 * 
 * Tests MongoDB text search functionality including
 * text queries, language options, and search scoring.
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\MongoQuery;

echo "🧪 MONGODB TEXT SEARCH: Running Tests\n";
echo str_repeat("=", 50) . "\n\n";

resetTestCounters();

try {
    // Test 1: Basic Text Search
    echo "1. Basic Text Search\n";
    $query = new MongoQuery();
    $result = $query
        ->from('articles')
        ->textSearch('mongodb database tutorial')
        ->toQuery();
    
    $expected = [
        [
            '$match' => [
                '$text' => [
                    '$search' => 'mongodb database tutorial'
                ]
            ]
        ]
    ];
    
    validateEquals(
        'Basic text search',
        $expected,
        $result,
        'Text search should create proper aggregation pipeline'
    );

    // Test 2: Text Search with Language
    echo "\n2. Text Search with Language\n";
    $query = new MongoQuery();
    $result = $query
        ->from('documents')
        ->textSearch('database performance', ['$language' => 'english'])
        ->toQuery();
    
    $expected = [
        [
            '$match' => [
                '$text' => [
                    '$search' => 'database performance',
                    '$language' => 'english'
                ]
            ]
        ]
    ];
    
    validateEquals(
        'Text search with language',
        $expected,
        $result,
        'Text search should support language options'
    );

    // Test 3: Text Search with Case Sensitivity
    echo "\n3. Text Search with Case Sensitivity\n";
    $query = new MongoQuery();
    $result = $query
        ->from('content')
        ->textSearch('MongoDB Tutorial', [
            '$language' => 'english',
            '$caseSensitive' => false,
            '$diacriticSensitive' => false
        ])
        ->toQuery();
    
    $expected = [
        [
            '$match' => [
                '$text' => [
                    '$search' => 'MongoDB Tutorial',
                    '$language' => 'english',
                    '$caseSensitive' => false,
                    '$diacriticSensitive' => false
                ]
            ]
        ]
    ];
    
    validateEquals(
        'Text search with sensitivity options',
        $expected,
        $result,
        'Text search should support case and diacritic sensitivity'
    );

    // Test 4: Text Search with Score Projection
    echo "\n4. Text Search with Score Projection\n";
    $query = new MongoQuery();
    $result = $query
        ->from('articles')
        ->textSearch('database optimization')
        ->project([
            'title' => 1,
            'content' => 1,
            'score' => ['$meta' => 'textScore']
        ])
        ->sortAggregation(['score' => ['$meta' => 'textScore']])
        ->toQuery();
    
    // Verify structure
    validateTrue(
        'Text search with score structure',
        is_array($result) && count($result) >= 3,
        'Should have match, project, and sort stages'
    );
    
    validateTrue(
        'Text search has text match',
        isset($result[0]['$match']['$text']),
        'Should have text search match stage'
    );
    
    validateTrue(
        'Text search has score projection',
        isset($result[1]['$project']['score']['$meta']),
        'Should project text score'
    );

    // Test 5: Text Search Combined with Other Filters
    echo "\n5. Text Search Combined with Filters\n";
    $query = new MongoQuery();
    $result = $query
        ->from('blog_posts')
        ->textSearch('javascript react tutorial')
        ->match([
            'status' => 'published',
            'category' => 'programming',
            'publish_date' => ['$gte' => '2024-01-01']
        ])
        ->project(['title', 'excerpt', 'author', 'publish_date'])
        ->sortAggregation(['publish_date' => -1])
        ->limitAggregation(20)
        ->toQuery();
    
    // Verify complex structure
    validateTrue(
        'Complex text search structure',
        is_array($result) && count($result) >= 4,
        'Should have multiple aggregation stages'
    );
    
    validateTrue(
        'Complex text search has filters',
        isset($result[1]['$match']['status']),
        'Should have additional filters after text search'
    );

    // Test 6: Text Search Phrase Matching
    echo "\n6. Text Search Phrase Matching\n";
    $query = new MongoQuery();
    $result = $query
        ->from('quotes')
        ->textSearch('"machine learning"')  // Phrase search
        ->toQuery();
    
    $expected = [
        [
            '$match' => [
                '$text' => [
                    '$search' => '"machine learning"'
                ]
            ]
        ]
    ];
    
    validateEquals(
        'Text search phrase matching',
        $expected,
        $result,
        'Text search should support phrase matching with quotes'
    );

    // Test 7: Text Search with Exclusion
    echo "\n7. Text Search with Exclusion\n";
    $query = new MongoQuery();
    $result = $query
        ->from('articles')
        ->textSearch('database -mysql -postgresql')  // Exclude mysql and postgresql
        ->toQuery();
    
    $expected = [
        [
            '$match' => [
                '$text' => [
                    '$search' => 'database -mysql -postgresql'
                ]
            ]
        ]
    ];
    
    validateEquals(
        'Text search with exclusion',
        $expected,
        $result,
        'Text search should support term exclusion with minus operator'
    );

    // Test 8: Full-text Search Analytics
    echo "\n8. Full-text Search Analytics\n";
    $query = new MongoQuery();
    $result = $query
        ->from('search_logs')
        ->textSearch('performance optimization')
        ->match(['timestamp' => ['$gte' => '2024-01-01']])
        ->group('search_query', [
            'count' => ['$sum' => 1],
            'avg_score' => ['$avg' => ['$meta' => 'textScore']],
            'unique_users' => ['$addToSet' => '$user_id']
        ])
        ->project([
            'search_query' => '$_id',
            'search_count' => '$count',
            'avg_relevance' => '$avg_score',
            'unique_users_count' => ['$size' => '$unique_users']
        ])
        ->sortAggregation(['search_count' => -1])
        ->limitAggregation(10)
        ->toQuery();
    
    // Verify analytics structure
    validateTrue(
        'Text search analytics structure',
        is_array($result) && count($result) >= 5,
        'Should have comprehensive analytics pipeline'
    );

    // Test 9: JSON Validation for Text Search
    echo "\n9. JSON Validation for Text Search\n";
    $query = new MongoQuery();
    $result = $query
        ->from('searchable_content')
        ->textSearch('mongodb aggregation pipeline', [
            '$language' => 'english',
            '$caseSensitive' => false
        ])
        ->toQuery();
    
    // Validate JSON serialization
    $json = json_encode($result);
    $decoded = json_decode($json, true);
    
    validateEquals(
        'Text search JSON validation',
        $result,
        $decoded,
        'Text search queries should be JSON serializable'
    );

} catch (Exception $e) {
    recordFailedTest('MongoDB Text Search Test', $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
$results = printTestSummary('MongoDB Text Search Tests');
return $results;
