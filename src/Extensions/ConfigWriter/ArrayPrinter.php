<?php

namespace RonasIT\ProjectInitializator\Extensions\ConfigWriter;

use PhpParser\ParserAbstract;
use Winter\LaravelConfigWriter\Printer\ArrayPrinter as BaseArrayPrinter;
use PhpParser\Node\Stmt\InlineHTML;

//TODO: remove this class after resolving https://github.com/wintercms/laravel-config-writer/issues/10
class ArrayPrinter extends BaseArrayPrinter
{
    public function render(array $stmts, ParserAbstract $parser): string
    {
        if (!$stmts) {
            return "<?php\n\n";
        }

        $this->parser = $parser;

        $code = "<?php\n\n" . $this->prettyPrint($stmts);

        $code = preg_replace('/(;\n?)(return\s*\[)/', ";\n\n$2", $code, 1);

        if ($stmts[0] instanceof InlineHTML) {
            $code = preg_replace('/^<\?php\s+\?>\n?/', '', $code);
        }

        if ($stmts[count($stmts) - 1] instanceof InlineHTML) {
            $code = preg_replace('/<\?php$/', '', rtrim($code));
        }

        $this->parser = null;

        return $code;
    }
}
