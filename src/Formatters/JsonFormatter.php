<?php

namespace Differ\Formatters\JsonFormatter;

function format(array $ast)
{
    return json_encode($ast, JSON_PRETTY_PRINT);
}
