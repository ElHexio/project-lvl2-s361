<?php

namespace Differ\Formatters\PrettyFormatter;

use function Differ\Utils\stringifyValue;

function format(array $ast, int $level = 0)
{
    $offset = str_pad('', $level * 4, ' ');
    $diff = array_reduce($ast, function ($diff, $node) use ($offset, $level) {
        switch ($node['type']) {
            case 'added':
                $after = prepareValueForDiff($node['after'], $level + 1);
                $diff[] = "{$offset}  + {$node['name']}: {$after}";
                break;
            case 'removed':
                $before = prepareValueForDiff($node['before'], $level + 1);
                $diff[] = "{$offset}  - {$node['name']}: {$before}";
                break;
            case 'unchanged':
                $before = prepareValueForDiff($node['before'], $level + 1);
                $diff[] = "{$offset}    {$node['name']}: {$before}";
                break;
            case 'nested':
                $value = format($node['children'], $level + 1);
                $diff[] = "{$offset}    {$node['name']}: {$value}";
                break;
            case 'changed':
                $after = prepareValueForDiff($node['after'], $level + 1);
                $diff[] = "{$offset}  + {$node['name']}: {$after}";
                $before = prepareValueForDiff($node['before'], $level + 1);
                $diff[] = "{$offset}  - {$node['name']}: {$before}";
                break;
        }

        return $diff;
    }, ['{']);
    $diff[] = "{$offset}}";

    return implode(PHP_EOL, $diff);
}

function prepareValueForDiff($value, $level)
{
    if (is_array($value)) {
        $offset = str_pad('', $level * 4, ' ');
        $lines = array_reduce(array_keys($value), function ($lines, $prop) use ($value, $offset, $level) {
            $preparedValue = prepareValueForDiff($value[$prop], $level + 1);
            $lines[] = "{$offset}    {$prop}: {$preparedValue}";
            return $lines;
        }, ["{"]);
        $lines[] = "{$offset}}";

        return implode(PHP_EOL, $lines);
    }

    return stringifyValue($value);
}
