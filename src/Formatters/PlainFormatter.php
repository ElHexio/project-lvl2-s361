<?php

namespace Differ\Formatters\PlainFormatter;

use function Differ\Utils\stringifyValue;

function format(array $ast, $path = '')
{
    $diff = array_reduce($ast, function ($diff, $node) use ($path) {
        $before = is_array($node['before']) ? 'complex value' : stringifyValue($node['before']);
        $after = is_array($node['after']) ? 'complex value' : stringifyValue($node['after']);
        switch ($node['type']) {
            case 'added':
                $diff[] = "Property '{$path}{$node['name']}' was added with value: '{$after}'";
                break;
            case 'removed':
                $diff[] = "Property '{$path}{$node['name']}' was removed";
                break;
            case 'changed':
                $diff[] = "Property '{$path}{$node['name']}' was changed. From '{$before}' to '{$after}'";
                break;
            case 'nested':
                $diff[] = format($node['before'], "{$path}{$node['name']}.");
                break;
        }

        return $diff;
    }, []);

    return implode(PHP_EOL, $diff);
}
