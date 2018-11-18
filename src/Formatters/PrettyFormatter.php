<?php

namespace Differ\Formatters\PrettyFormatter;

use function Differ\Utils\stringifyValue;

function format(array $ast, int $level = 0)
{
    $offset = str_pad('', $level * 4, ' ');
    $diff = array_reduce($ast, function ($diff, $node) use ($offset, $level) {
        [$before, $after] = normalizeDiffValues($node['before'], $node['after'], $level);
        switch ($node['type']) {
            case 'added':
                $diff[] = "{$offset}  + {$node['name']}: {$after}";
                break;
            case 'removed':
                $diff[] = "{$offset}  - {$node['name']}: {$before}";
                break;
            case 'unchanged':
            case 'nested':
                $diff[] = "{$offset}    {$node['name']}: {$before}";
                break;
            case 'changed':
                $diff[] = "{$offset}  + {$node['name']}: {$after}";
                $diff[] = "{$offset}  - {$node['name']}: {$before}";
                break;
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
