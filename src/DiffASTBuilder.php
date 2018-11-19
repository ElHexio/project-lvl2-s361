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
            $ast[] = ['type' => 'added', 'name' => $name, 'after' => $afterValue];
            return $ast;
        }

        $wasRemoved = !array_key_exists($name, $after);
        if ($wasRemoved) {
            $ast[] = ['type' => 'removed', 'name' => $name, 'before' => $beforeValue];
            return $ast;
        }

        $hasChildrenBeforeAndAfter = is_array($beforeValue) && is_array($afterValue);
        if ($hasChildrenBeforeAndAfter) {
            $children = buildDiffAST($beforeValue, $afterValue);
            $ast[] = ['type' => 'nested', 'name' => $name,
                      'before' => $beforeValue, 'after' => $afterValue, 'children' => $children];
            return $ast;
        }

        if ($beforeValue === $afterValue) {
            $ast[] = ['type' => 'unchanged', 'name' => $name, 'before' => $beforeValue, 'after' => $afterValue];
            return $ast;
        }

        $ast[] = ['type' => 'changed', 'name' => $name, 'before' => $beforeValue, 'after' => $afterValue];
        return $ast;
    }, []);

    return $ast;
}
