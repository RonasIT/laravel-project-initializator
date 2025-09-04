<?php

namespace RonasIT\ProjectInitializator\Support\Parser\Visitors\ArrayVisitors\PropertyArrayVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;

class RemoveValueFromArrayPropertyPropertyArrayVisitor extends BasePropertyArrayVisitor
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

        $array->items = $this->getTargetItems($array->items);
    }
}