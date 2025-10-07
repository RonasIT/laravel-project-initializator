<?php

namespace RonasIT\ProjectInitializator\ConfigWriter;

use PhpParser\Node\Stmt;
use PhpParser\ParserAbstract;
use Winter\LaravelConfigWriter\Printer\ArrayPrinter as BaseArrayPrinter;

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

        if ($stmts[0] instanceof Stmt\InlineHTML) {
            $code = preg_replace('/^<\?php\s+\?>\n?/', '', $code);
        }

        if ($stmts[count($stmts) - 1] instanceof Stmt\InlineHTML) {
            $code = preg_replace('/<\?php$/', '', rtrim($code));
        }

        $this->parser = null;

        return $code;
    }
}
