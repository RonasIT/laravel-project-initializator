<?php

namespace RonasIT\ProjectInitializator\Support\Visitors\ArrayVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Scalar\String_;

class RemoveValueFromArrayPropertyVisitor extends BaseArrayVisitor
{
    public function enterNode(Node $node)
    {
        if ($node instanceof Property) {
            $propName = $node->props[0]->name->toString();

            if (in_array($propName, $this->stmtNames, true) && $node->props[0]->default instanceof Node\Expr\Array_) {
                $array = $node->props[0]->default;

                $array->items = array_values(array_filter(
                    $array->items,
                    function ($item) {
                        return !(
                            $item->value instanceof String_
                            && $item->value->value === $this->value
                        );
                    }
                ));
            }
        }
        return null;
    }
}