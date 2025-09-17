<?php

namespace RonasIT\ProjectInitializator\Support\Parser\Visitors\ArrayVisitors\PropertyArrayVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\ArrayItem;

class AddValueToArrayPropertyPropertyArrayVisitor extends BasePropertyArrayVisitor
{
    public function enterNode(Node $node): void
    {
        if (!$node instanceof Property) {
            return;
        }

        $property = $node->props[0];

        $propertyName = $property->name->toString();

        if (!$this->isTargetProperty($propertyName, $property->default)) {
            return;
        }

        $array = $property->default;

        if ($this->getTargetItems($array->items) !== $array->items) {
            return;
        }

        $array->items[] = new ArrayItem(new String_($this->value));
    }
}