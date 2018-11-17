<?php

namespace Differ\DiffASTBuilder;

function buildDiffAST(array $before, array $after)
{
    $allPropertiesNames = array_unique(array_merge(array_keys($before), array_keys($after)));
    $ast = array_reduce($allPropertiesNames, function ($ast, $name) use ($before, $after) {
        $beforeValue = isset($before[$name]) ? $before[$name] : null;
        $afterValue = isset($after[$name]) ? $after[$name] : null;

        $isPropAdded = !array_key_exists($name, $before);
        $isPropRemoved = !array_key_exists($name, $after);
        $isSameNodeBeforeAndAfterHasChildren = is_array($beforeValue) && is_array($afterValue);

        $state = 'changed';
        if ($isPropAdded || $isPropRemoved) {
            $state = $isPropAdded ? 'added' : 'removed';
        } elseif ($beforeValue === $afterValue || $isSameNodeBeforeAndAfterHasChildren) {
            $state = 'unchanged';
        }
        $ast[] = prepareDiffNode($state, $name, $beforeValue, $afterValue);

        return $ast;
    }, []);

    return $ast;
}

function prepareDiffNode(string $state, string $name, $before, $after)
{
    $isUnchangedNodeHasChildrenThatMayDiffer = $state === 'unchanged' && is_array($before) && is_array($after);
    if ($isUnchangedNodeHasChildrenThatMayDiffer) {
        $beforeValue = buildDiffAST($before, $after);
        $afterValue = null;
    } else {
        $afterValue = is_array($after) ? buildDiffAST($after, $after) : $after;
        $beforeValue = is_array($before) ? buildDiffAST($before, $before) : $before;
    }

    return ['state' => $state, 'name' => $name, 'before' => $beforeValue, 'after' => $afterValue];
}
