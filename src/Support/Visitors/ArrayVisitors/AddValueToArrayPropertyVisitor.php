<?php

namespace RonasIT\ProjectInitializator\Support\Visitors\ArrayVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Array_;

class AddValueToArrayPropertyVisitor extends BaseArrayVisitor
{
    public function enterNode(Node $node)
    {
        if ($node instanceof Property) {
            $propName = $node->props[0]->name->toString();

            if (in_array($propName, $this->stmtNames, true) && $node->props[0]->default instanceof Array_) {
                $array = $node->props[0]->default;

                foreach ($array->items as $item) {
                    if ($item->value instanceof String_
                        && $item->value->value === $this->value) {
                        return null;
                    }
                }

                $array->items[] = new ArrayItem(
                    new Node\Scalar\String_($this->value)
                );
            }
        }

        return null;
    }
}