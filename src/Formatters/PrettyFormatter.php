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
                $children = format($node['children'], $level + 1);
                $diff[] = "{$offset}    {$node['name']}: {$children}";
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
    return is_array($value) ? stringifyArray($value, $level) : stringifyValue($value);
}

function stringifyArray(array $items, $level)
{
    $offset = str_pad('', $level * 4, ' ');
    $properties = array_keys($items);
    $lines = array_reduce($properties, function ($lines, $prop) use ($items, $offset, $level) {
        $preparedValue = prepareValueForDiff($items[$prop], $level + 1);
        $lines[] = "{$offset}    {$prop}: {$preparedValue}";
        return $lines;
    }, ["{"]);
    $lines[] = "{$offset}}";

    return implode(PHP_EOL, $lines);
}
