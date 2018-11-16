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
}
