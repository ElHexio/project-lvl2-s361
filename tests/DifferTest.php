<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

use function \Differ\getDiff;

class DifferTest extends TestCase
{
    public function testGeneratePrettyDiffForJsonFiles()
    {
        $diff = getDiff(
            __DIR__ . '/fixtures/json/before.json',
            __DIR__ . '/fixtures/json/after.json',
            'pretty'
        );
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/diff/expected_pretty.diff', $diff);

        $nestedDiff = getDiff(
            __DIR__ . '/fixtures/json/nested_before.json',
            __DIR__ . '/fixtures/json/nested_after.json'
        );
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/diff/nested_expected_pretty.diff', $nestedDiff);
    }

    public function testGeneratePlainDiffForJsonFiles()
    {
        $diff = getDiff(
            __DIR__ . '/fixtures/json/before.json',
            __DIR__ . '/fixtures/json/after.json',
            'plain'
        );
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/diff/expected_plain.diff', $diff);

        $nestedDiff = getDiff(
            __DIR__ . '/fixtures/json/nested_before.json',
            __DIR__ . '/fixtures/json/nested_after.json',
            'plain'
        );
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/diff/nested_expected_plain.diff', $nestedDiff);
    }

    public function testGeneratePrettyDiffForYamlFiles()
    {
        $diff = getDiff(
            __DIR__ . '/fixtures/yaml/before.yaml',
            __DIR__ . '/fixtures/yaml/after.yaml'
        );
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/diff/expected_pretty.diff', $diff);

        $nestedDiff = getDiff(
            __DIR__ . '/fixtures/yaml/nested_before.yaml',
            __DIR__ . '/fixtures/yaml/nested_after.yaml',
            'pretty'
        );
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/diff/nested_expected_pretty.diff', $nestedDiff);
    }

    public function testGeneratePlainDiffForYamlFiles()
    {
        $diff = getDiff(
            __DIR__ . '/fixtures/yaml/before.yaml',
            __DIR__ . '/fixtures/yaml/after.yaml',
            'plain'
        );
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/diff/expected_plain.diff', $diff);

        $nestedDiff = getDiff(
            __DIR__ . '/fixtures/yaml/nested_before.yaml',
            __DIR__ . '/fixtures/yaml/nested_after.yaml',
            'plain'
        );
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/diff/nested_expected_plain.diff', $nestedDiff);
    }
}
