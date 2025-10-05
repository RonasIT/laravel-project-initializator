<?php

namespace RonasIT\ProjectInitializator\ConfigWriter;

use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\Parser\Php7;
use PhpParser\Parser\Php8;
use PhpParser\PhpVersion;
use Winter\LaravelConfigWriter\ArrayFile as BaseArrayFile;
use Winter\LaravelConfigWriter\Exceptions\ConfigWriterException;

class ArrayFile extends BaseArrayFile
{
    public static function open(string $filePath, bool $throwIfMissing = false)
    {
        $exists = file_exists($filePath);

        if (!$exists && $throwIfMissing) {
            throw new \InvalidArgumentException('file not found');
        }

        $version = PhpVersion::getHostVersion();

        $lexer = new Lexer\Emulative($version);
        $parser = ($version->id >= 80000)
            ? new Php8($lexer, $version)
            : new Php7($lexer, $version);

        try {
            $ast = $parser->parse(
                $exists
                    ? file_get_contents($filePath)
                    : sprintf('<?php%1$s%1$sreturn [];%1$s', "\n")
            );
        } catch (Error $e) {
            throw new ConfigWriterException($e);
        }

        return new static($ast, $parser, $filePath, new ArrayPrinter());
    }
}
