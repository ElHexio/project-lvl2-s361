<?php

namespace Differ\DiffASTBuilder;

function buildDiffAST(array $before, array $after)
{
    $allPropertiesNames = array_unique(array_merge(array_keys($before), array_keys($after)));
    $ast = array_reduce($allPropertiesNames, function ($ast, $name) use ($before, $after) {
        $beforeValue = $before[$name] ?? null;
        $afterValue = $after[$name] ?? null;

        $wasAdded = !array_key_exists($name, $before);
        if ($wasAdded) {
            $ast[] = prepareDiffNode('added', $name, null, $afterValue);
            return $ast;
        }

        $wasRemoved = !array_key_exists($name, $after);
        if ($wasRemoved) {
            $ast[] = prepareDiffNode('removed', $name, $beforeValue, null);
            return $ast;
        }

        if ($beforeValue === $afterValue) {
            $ast[] = prepareDiffNode('unchanged', $name, $beforeValue, $afterValue);
            return $ast;
        }

        $hasChildrenBeforeAndAfter = is_array($beforeValue) && is_array($afterValue);
        if ($hasChildrenBeforeAndAfter) {
            $ast[] = prepareDiffNode('nested', $name, $beforeValue, $afterValue);
            return $ast;
        }

        $ast[] = prepareDiffNode('changed', $name, $beforeValue, $afterValue);
        return $ast;
    }, []);

    return $ast;
}

function prepareDiffNode(string $type, string $name, $before, $after)
{
    if ($type === 'nested') {
        $beforeValue = buildDiffAST($before, $after);
        $afterValue = null;
    } else {
        $afterValue = is_array($after) ? buildDiffAST($after, $after) : $after;
        $beforeValue = is_array($before) ? buildDiffAST($before, $before) : $before;
    }

    return ['type' => $type, 'name' => $name, 'before' => $beforeValue, 'after' => $afterValue];
}
