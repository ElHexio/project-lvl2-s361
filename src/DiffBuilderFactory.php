<?php

namespace Differ\DiffBuilderFactory;

use function \Differ\Formatters\PrettyFormatter\format as formatPretty;
use function \Differ\Formatters\PlainFormatter\format as formatPlain;
use function \Differ\Formatters\JsonFormatter\format as formatJson;

function getDiffBuilder($format)
{
    switch ($format) {
        case 'pretty':
            return function (array $ast) {
                return formatPretty($ast);
            };
        case 'plain':
            return function (array $ast) {
                return formatPlain($ast);
            };
        case 'json':
            return function (array $ast) {
                return formatJson($ast);
            };
        default:
            throw new \RuntimeException('Cannot find diff generator for specified format');
    }
}
