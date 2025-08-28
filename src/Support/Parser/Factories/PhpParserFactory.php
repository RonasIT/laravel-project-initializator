<?php

namespace RonasIT\ProjectInitializator\Support\Parser\Factories;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use RonasIT\ProjectInitializator\Support\Parser\PhpParser;

class PhpParserFactory
{
    public static function create(string $filePath): PhpParser
    {
        return new PhpParser(
            $filePath,
            (new ParserFactory())->createForNewestSupportedVersion(),
            new NodeTraverser(),
            new Standard(),
        );
    }
}