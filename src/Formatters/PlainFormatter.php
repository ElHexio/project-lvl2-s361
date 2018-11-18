<?php

namespace Differ\Formatters\PlainFormatter;

function format(array $ast, $path = '')
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
            $diff[] = format($node['before'], "{$path}{$node['name']}.");
        }
        return $diff;
    }, []);

    return implode(PHP_EOL, $diff);
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

