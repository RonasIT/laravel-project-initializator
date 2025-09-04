<?php

namespace RonasIT\ProjectInitializator\Support\Parser\Visitors\ArrayVisitors\MethodReturnArrayVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

class RemoveValueFromMethodReturnArrayVisitor extends BaseMethodReturnArrayVisitor
{
    public function enterNode(Node $node): void
    {
        if (!($node instanceof ClassMethod)) {
            return;
        }

        $methodName = $node->name->toString();

        if (!in_array($methodName, $this->stmtNames, true)) {
            return;
        }

        foreach ($node->stmts as $stmt) {
            if ($this->isTargetReturnArray($stmt)) {
                $stmt->expr->items = $this->getTargetItems($stmt->expr->items);
            }
        }
    }
}