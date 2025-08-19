<?php

namespace RonasIT\ProjectInitializator\Support\Visitors\MethodVisitors;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

class AppendPartToMethodVisitor extends NodeVisitorAbstract
{
    public function __construct(
        protected string $methodName,
        protected Node $stmtToAdd,
    )
    {
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof ClassMethod && $node->name->toString() === $this->methodName) {
            $node->stmts[] = $this->stmtToAdd;
        }
    }
}