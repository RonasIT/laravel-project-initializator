<?php

namespace RonasIT\ProjectInitializator\Support\Parser\Visitors\ArrayVisitors\PropertyArrayVisitors;

use PhpParser\Node\Expr\ArrayItem;
use RonasIT\ProjectInitializator\Support\Parser\Visitors\ArrayVisitors\BaseArrayVisitor;

class BasePropertyArrayVisitor extends BaseArrayVisitor
{
    protected function isTargetItem(?ArrayItem $item): bool
    {
        return $item !== null
            && $item->value->value === $this->value;
    }
}