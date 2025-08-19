<?php

namespace RonasIT\ProjectInitializator\Support\Visitors\ArrayVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;

class RemoveValueFromMethodReturnArrayVisitor extends BaseArrayVisitor
{
    public function enterNode(Node $node)
    {
        if ($node instanceof ClassMethod) {
            $methodName = $node->name->toString();

            if (in_array($methodName, $this->stmtNames, true)) {
                foreach ($node->stmts as $stmt) {
                    if ($stmt instanceof Return_ && $stmt->expr instanceof Array_) {
                        $stmt->expr->items = array_values(
                            array_filter(
                                $stmt->expr->items,
                                function ($item) {
                                    return !(
                                        $item->key instanceof String_
                                        && $item->key->value === $this->value
                                    );
                                }
                            )
                        );
                    }
                }
            }
        }
        return null;
    }
}