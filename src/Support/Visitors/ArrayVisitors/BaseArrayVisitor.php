<?php

namespace RonasIT\ProjectInitializator\Support\Visitors\ArrayVisitors;

use PhpParser\NodeVisitorAbstract;

class BaseArrayVisitor extends NodeVisitorAbstract
{
    public function __construct(
        protected array $stmtNames,
        protected string $value,
    )
    {
    }
}