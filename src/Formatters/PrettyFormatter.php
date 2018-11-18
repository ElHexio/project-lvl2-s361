<?php

namespace Differ\Formatters\PrettyFormatter;

function format(array $ast, int $level = 0)
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
    $beforeValue = is_array($before) ? format($before, $level + 1) : stringifyValue($before);
    $afterValue = is_array($after) ? format($after, $level + 1) : stringifyValue($after);
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
