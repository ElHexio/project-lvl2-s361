<?php

namespace Differ;

use function Differ\FileParserFactory\getParser;
use function Differ\DiffASTBuilder\buildDiffAST;
use function Differ\DiffBuilderFactory\getDiffBuilder;

function getDiff(string $firstFile, string $secondFile, string $format = 'pretty'): string
{
    $firstFileExtension = pathinfo($firstFile, PATHINFO_EXTENSION);
    if ($firstFileExtension !== pathinfo($secondFile, PATHINFO_EXTENSION)) {
        throw new \Exception('Cannot generate diff for files of different type');
    }

    $parse = getParser($firstFileExtension);
    $before = $parse(file_get_contents($firstFile));
    $after = $parse(file_get_contents($secondFile));
    $ast = buildDiffAST($before, $after);

    $buildDiff = getDiffBuilder($format);
    $diff = $buildDiff($ast);

    return $diff;
}
