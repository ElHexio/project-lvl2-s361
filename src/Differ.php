<?php

namespace Differ;

use function Differ\FileParserFactory\getParser;
use function Differ\DiffBuilderFactory\getDiffBuilder;

function getDiff(string $firstFile, string $secondFile, string $format = 'pretty'): string
{
    $firstFileExtension = pathinfo($firstFile, PATHINFO_EXTENSION);
    if ($firstFileExtension !== pathinfo($secondFile, PATHINFO_EXTENSION)) {
        return '';
    }

    $parse = getParser($firstFileExtension);
    $before = $parse(file_get_contents($firstFile));
    $after = $parse(file_get_contents($secondFile));
    $ast = buildDiffAST($before, $after);

    $build = getDiffBuilder($format);
    $diff = $build($ast);

    return $diff;
}

function buildDiffAST(array $before, array $after)
{
    $allPropertiesNames = array_unique(array_merge(array_keys($before), array_keys($after)));
    $ast = array_reduce($allPropertiesNames, function ($acc, $name) use ($before, $after) {
        $beforeValue = array_key_exists($name, $before) ? $before[$name] : null;
        $afterValue = array_key_exists($name, $after) ? $after[$name] : null;

        $isPropAdded = !array_key_exists($name, $before);
        $isPropRemoved = !array_key_exists($name, $after);
        if ($isPropAdded) {
            $acc[] = ['state' => 'added', 'name' => $name, 'before' => null,
                      'after' => is_array($afterValue) ? buildDiffAST($afterValue, $afterValue) : $afterValue];
        } elseif ($isPropRemoved) {
            $acc[] = ['state' => 'removed', 'name' => $name,
                      'before' => is_array($beforeValue) ? buildDiffAST($beforeValue, $beforeValue) : $beforeValue,
                      'after' => null];
        } elseif (is_array($beforeValue) && is_array($afterValue)) {
            $acc[] = ['state' => 'unchanged', 'name' => $name,
                      'before' => buildDiffAST($beforeValue, $afterValue), 'after' => null];
        } elseif ($beforeValue === $afterValue) {
            $acc[] = ['state' => 'unchanged', 'name' => $name,
                      'before' => $beforeValue, 'after' => $afterValue];
        } else {
            $acc[] = ['state' => 'changed', 'name' => $name,
                      'before' => is_array($beforeValue) ? buildDiffAST($beforeValue, $beforeValue) : $beforeValue,
                      'after' => is_array($afterValue) ? buildDiffAST($afterValue, $afterValue) : $afterValue];
        }
        return $acc;
    }, []);

    return $ast;
}
