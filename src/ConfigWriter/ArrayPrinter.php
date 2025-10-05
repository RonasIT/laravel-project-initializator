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

        $p = "<?php\n\n" . $this->prettyPrint($stmts);

        $p = preg_replace('/(;\n?)(return\s*\[)/', ";\n\n$2", $p, 1);

        if ($stmts[0] instanceof Stmt\InlineHTML) {
            $p = preg_replace('/^<\?php\s+\?>\n?/', '', $p);
        }

        if ($stmts[count($stmts) - 1] instanceof Stmt\InlineHTML) {
            $p = preg_replace('/<\?php$/', '', rtrim($p));
        }

        $this->parser = null;

        return $p;
    }
}
