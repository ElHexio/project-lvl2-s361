<?php

namespace App;

use Docopt\Handler;

use function Differ\getDiff;

function run()
{
    $args = (new Handler())->handle(getDoc());
    echo getDiff($args['<firstFile>'], $args['<secondFile>'], $args['--format']);
    echo PHP_EOL;
}

function getDoc()
{
    return <<<DOC
Generate diff

Usage:
    gendiff [--format <fmt>] <firstFile> <secondFile>
    gendiff (-h|--help)

Options:
    -h --help                     Show this screen
    --format <fmt>                Report format [default: pretty]

DOC;
}
