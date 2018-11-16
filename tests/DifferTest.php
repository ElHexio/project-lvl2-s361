<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

use function \Differ\getDiff;

class DifferTest extends TestCase
{
    public function testGeneratePrettyDiffForJsonFiles()
    {
        $diff = getDiff(__DIR__ . '/fixtures/json/before.json', __DIR__ . '/fixtures/json/after.json', 'pretty');
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/json/expected_pretty.diff', $diff);

        $diff = getDiff(__DIR__ . '/fixtures/json/nested_before.json', __DIR__ . '/fixtures/json/nested_after.json');
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/json/nested_expected_pretty.diff', $diff);
    }

    public function testGeneratePlainDiffFromJsonFiles()
    {
        $diff = getDiff(
            __DIR__ . '/fixtures/json/nested_before.json',
            __DIR__ . '/fixtures/json/nested_after.json',
            'plain'
        );
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/json/nested_expected_plain.diff', $diff);
    }

    public function testGenerateDiffForYamlFiles()
    {
        $diff = getDiff(__DIR__ . '/fixtures/yaml/before.yaml', __DIR__ . '/fixtures/yaml/after.yaml');
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/yaml/expected.diff', $diff);
    }

    public function testBuildFromNewTypeOfAST()
    {
        $ast = [
            ['state' => 'added', 'name' => 'key', 'before' => null, 'after' => 'value'],
            ['state' => 'removed', 'name' => 'key2', 'before' => 'value2', 'after' => null],
            ['state' => 'changed', 'name' => 'key3', 'before' => 'value3', 'after' => 'value3_1'],
            ['state' => 'unchanged', 'name' => 'key4', 'before' => 'value4', 'after' => 'value4'],
            ['state' => 'unchanged', 'name' => 'group1', 'before' => [
                ['state' => 'added', 'name' => 'key1', 'before' => null, 'after' => 'value1'],
                ['state' => 'removed', 'name' => 'key2', 'before' => 'value2', 'after' => null],
            ], 'after' => null],
            ['state' => 'added', 'name' => 'group2', 'before' => null, 'after' => [
                ['state' => 'unchanged', 'name' => 'key', 'before' => 'value', 'after' => 'value'],
                ['state' => 'unchanged', 'name' => 'key1', 'before' => 'value1', 'after' => 'value1'],
            ]],
            ['state' => 'removed', 'name' => 'group3', 'before' => [
                ['state' => 'unchanged', 'name' => 'key', 'before' => 'value', 'after' => 'value'],
                ['state' => 'unchanged', 'name' => 'key1', 'before' => 'value1', 'after' => 'value1'],
            ], 'after' => null],
            ['state' => 'unchanged', 'name' => 'group4', 'before' => [
                ['state' => 'unchanged', 'name' => 'group1', 'before' => [
                    ['state' => 'added', 'name' => 'key1', 'before' => null, 'after' => 'value1'],
                    ['state' => 'removed', 'name' => 'key2', 'before' => 'value2', 'after' => null],
                ], 'after' => null],
            ], 'after' => null],
        ];
        $diff = [
            '{',
            '  + key: value',
            '  - key2: value2',
            '  + key3: value3_1',
            '  - key3: value3',
            '    key4: value4',
            '    group1: {',
            '      + key1: value1',
            '      - key2: value2',
            '    }',
            '  + group2: {',
            '        key: value',
            '        key1: value1',
            '    }',
            '  - group3: {',
            '        key: value',
            '        key1: value1',
            '    }',
            '    group4: {',
            '        group1: {',
            '          + key1: value1',
            '          - key2: value2',
            '        }',
            '    }',
            '}'
        ];
        $this->assertEquals(implode(PHP_EOL, $diff), \Differ\buildDiffFromNewAST($ast));
    }
}
