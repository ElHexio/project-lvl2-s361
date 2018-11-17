<?php

namespace Differ\DiffBuilderFactory;

function getDiffBuilder($format)
{
    switch ($format) {
        case 'pretty':
            return function (array $ast) {
                return buildPrettyDiff($ast);
            };
        case 'plain':
            return function (array $ast) {
                return buildPlainDiff($ast);
            };
        case 'json':
            return function (array $ast) {
                return json_encode($ast, JSON_PRETTY_PRINT);
            };
        default:
            throw new \RuntimeException('Cannot find diff generator for specified format');
    }
}

function buildPrettyDiff(array $ast, int $level = 0)
{
    $offset = str_pad('', $level * 4, ' ');
    $diff = array_reduce($ast, function ($diff, $node) use ($offset, $level) {
        [$before, $after] = normalizeDiffValues($node['before'], $node['after'], $level);
        if ($node['state'] === 'added') {
            $diff[] = "{$offset}  + {$node['name']}: {$after}";
        } elseif ($node['state'] === 'removed') {
            $diff[] = "{$offset}  - {$node['name']}: {$before}";
        } elseif ($node['state'] === 'unchanged') {
            $diff[] = "{$offset}    {$node['name']}: {$before}";
        } elseif ($node['state'] === 'changed') {
            $diff[] = "{$offset}  + {$node['name']}: {$after}";
            $diff[] = "{$offset}  - {$node['name']}: {$before}";
        }
        return $diff;
    }, ['{']);
    $diff[] = "{$offset}}";

    return implode(PHP_EOL, $diff);
}

function normalizeDiffValues($before, $after, $level)
{
    $beforeValue = is_array($before) ? buildPrettyDiff($before, $level + 1) : stringifyValue($before);
    $afterValue = is_array($after) ? buildPrettyDiff($after, $level + 1) : stringifyValue($after);
    return [$beforeValue, $afterValue];
}

function stringifyValue($value): string
{
    $stringValue = $value;
    if (is_bool($value)) {
        $stringValue = $value ? 'true' : 'false';
    } elseif (is_null($value)) {
        $stringValue = 'null';
    }

    return $stringValue;
}

function buildPlainDiff(array $ast, $path = '')
{
    $diff = array_reduce($ast, function ($diff, $node) use ($path) {
        $before = is_array($node['before']) ? 'complex value' : stringifyValue($node['before']);
        $after = is_array($node['after']) ? 'complex value' : stringifyValue($node['after']);

        if ($node['state'] === 'added') {
            $diff[] = "Property '{$path}{$node['name']}' was added with value: '{$after}'";
        } elseif ($node['state'] === 'removed') {
            $diff[] = "Property '{$path}{$node['name']}' was removed";
        } elseif ($node['state'] === 'changed') {
            $diff[] = "Property '{$path}{$node['name']}' was changed. From '{$before}' to '{$after}'";
        } elseif ($node['state'] === 'unchanged' && is_array($node['before'])) {
            $diff[] = buildPlainDiff($node['before'], "{$path}{$node['name']}.");
        }
        return $diff;
    }, []);

    return implode(PHP_EOL, $diff);
}
