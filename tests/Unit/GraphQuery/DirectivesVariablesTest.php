<?php

namespace Tests\Unit\GraphQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\QueryCraft;

class DirectivesVariablesTest extends TestCase
{
    // public function testBuildVariablesAndDirectivesReturnStringsOrArrays()
    // {
    //     $q = QueryCraft::graph();
    //     $ref = new \ReflectionClass($q);

    //     // buildFragment expects (string $name, array $fragment)
    //     $methods = [
    //         ['name' => 'buildVariables', 'args' => []],
    //         ['name' => 'buildDirectives', 'args' => []],
    //         ['name' => 'buildFragment', 'args' => ['frag', ['type' => 'TestType', 'fields' => ['id']]]]
    //     ];

    //     foreach ($methods as $mdata) {
    //         $m = $ref->getMethod($mdata['name']);
    //         $m->setAccessible(true);
    //         $res = $m->invoke($q, ...$mdata['args']);
    //         $this->assertTrue(is_string($res) || is_array($res));
    //     }
    // }
}
