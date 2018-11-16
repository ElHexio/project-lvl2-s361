<?php

namespace Differ;

use function Differ\FileParserFactory\getParser;

const LINE_STATE_ADDED = 'added';
const LINE_STATE_REMOVED = 'removed';
const LINE_STATE_UNCHANGED = 'unchanged';
const LINE_STATE_FORMAT_MAP = [
    LINE_STATE_ADDED => '+',
    LINE_STATE_REMOVED => '-',
    LINE_STATE_UNCHANGED => ' ',
];

function getDiff(string $firstFile, string $secondFile): string
{
    $firstFileExtension = pathinfo($firstFile, PATHINFO_EXTENSION);
    if ($firstFileExtension !== pathinfo($secondFile, PATHINFO_EXTENSION)) {
        return '';
    }

    $parse = getParser($firstFileExtension);
    $before = $parse(file_get_contents($firstFile));
    $after = $parse(file_get_contents($secondFile));
    $ast = buildAST($before, $after);
    $diff = buildDiffFromAST($ast);

    return $diff;
}

function buildAST(array $before, array $after)
{
    $allPropertiesNames = array_unique(array_merge(array_keys($before), array_keys($after)));
    $ast = array_reduce($allPropertiesNames, function ($acc, $name) use ($before, $after) {
        [$beforeValue, $afterValue] = getNormalizedValues($name, $before, $after);

        if ($beforeValue === $afterValue) {
            $acc[] = prepareASTNode(LINE_STATE_UNCHANGED, $name, $beforeValue, $afterValue);
        } elseif ($afterValue === null || $beforeValue === null) {
            $state = $beforeValue === null ? LINE_STATE_ADDED : LINE_STATE_REMOVED;
            $value = $beforeValue ?? $afterValue;
            $acc[] = prepareASTNode($state, $name, $value, $value);
        } else {
            if (is_array($beforeValue) && is_array($afterValue)) {
                $acc[] = prepareASTNode(LINE_STATE_UNCHANGED, $name, $beforeValue, $afterValue);
            } elseif (is_array($before[$name])) {
                $acc[] = prepareASTNode(LINE_STATE_ADDED, $name, null, $afterValue);
                $acc[] = prepareASTNode(LINE_STATE_REMOVED, $name, $beforeValue, $beforeValue);
            } elseif (is_array($after[$name])) {
                $acc[] = prepareASTNode(LINE_STATE_ADDED, $name, $afterValue, $afterValue);
                $acc[] = prepareASTNode(LINE_STATE_REMOVED, $name, $beforeValue, null);
            } else {
                $acc[] = prepareASTNode(LINE_STATE_ADDED, $name, null, $afterValue);
                $acc[] = prepareASTNode(LINE_STATE_REMOVED, $name, $beforeValue, null);
            }
        }

        return $acc;
    }, []);

    return $ast;
}

function prepareASTNode($state, $name, $before, $after)
{
    $value = $before;
    if (is_array($before) && is_array($after)) {
        $value = buildAST($before, $after);
    } elseif ($before === null) {
        $value = $after;
    }

    return ['state' => $state, 'name' => $name, 'value' => $value];
}

function getNormalizedValues($name, $before, $after)
{
    $isKeyRemoved = !array_key_exists($name, $after);
    $isKeyAdded = !array_key_exists($name, $before);
    if ($isKeyRemoved) {
        $beforeValue = is_array($before[$name]) ? $before[$name] : stringifyValue($before[$name]);
        $afterValue = null;
    } elseif ($isKeyAdded) {
        $beforeValue = null;
        $afterValue = is_array($after[$name]) ? $after[$name] : stringifyValue($after[$name]);
    } else {
        $afterValue = is_array($after[$name]) ? $after[$name] : stringifyValue($after[$name]);
        $beforeValue = is_array($before[$name]) ? $before[$name] : stringifyValue($before[$name]);
    }

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

function buildDiffFromAST(array $ast, int $level = 0)
{
    $offset = str_pad('', $level * 4, ' ');
    $diffLines = array_reduce($ast, function ($acc, $item) use ($level, $offset) {
        $format = LINE_STATE_FORMAT_MAP[$item['state']];
        $value = is_array($item['value']) ? buildDiffFromAST($item['value'], $level + 1) : $item['value'];
        $acc[] = sprintf('%s  %s %s: %s', $offset, $format, $item['name'], $value);
        return $acc;
    }, ['{']);

    $diffLines[] = sprintf('%s}', $offset);

    return implode(PHP_EOL, $diffLines);
}

function buildDiffFromNewAST(array $ast, int $level = 0)
{
    $offset = str_pad('', $level * 4, ' ');
    $diff = ['{'];
    foreach ($ast as $node) {
        if ($node['state'] === 'added') {
            $value = is_array($node['after']) ? buildDiffFromNewAST($node['after'], $level + 1) : $node['after'];
            $diff[] = sprintf('%s  %s %s: %s', $offset, '+', $node['name'], $value);
        } elseif ($node['state'] === 'removed') {
            $value = is_array($node['before']) ? buildDiffFromNewAST($node['before'], $level + 1) : $node['before'];
            $diff[] = sprintf('%s  %s %s: %s', $offset, '-', $node['name'], $value);
        } elseif ($node['state'] === 'unchanged') {
            $value = $node['before'];
            $value = is_array($node['before']) ? buildDiffFromNewAST($node['before'], $level + 1) : $node['before'];
            $diff[] = sprintf('%s  %s %s: %s', $offset, ' ', $node['name'], $value);
        } elseif ($node['state'] === 'changed') {
            $value = is_array($node['after']) ? buildDiffFromNewAST($node['after'], $level + 1) : $node['after'];
            $diff[] = sprintf('%s  %s %s: %s', $offset, '+', $node['name'], $value);
            $value = is_array($node['before']) ? buildDiffFromNewAST($node['before'], $level + 1) : $node['before'];
            $diff[] = sprintf('%s  %s %s: %s', $offset, '-', $node['name'], $value);
        }
    }
    $diff[] = sprintf('%s}', $offset);

    return implode(PHP_EOL, $diff);
}
