<?php

namespace RonasIT\ProjectInitializator\Support\Parser\Visitors\ArrayVisitors\MethodReturnArrayVisitors;

use RonasIT\ProjectInitializator\Support\Parser\Visitors\ArrayVisitors\BaseArrayVisitor;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Expr\Array_;

class BaseMethodReturnArrayVisitor extends BaseArrayVisitor
{
    protected function isTargetItem(?ArrayItem $item): bool
    {
        return !empty($item)
            && $item->key instanceof String_
            && $item->key->value === $this->value;
    }

    protected function isTargetReturnArray(Node $stmt): bool
    {
        return $stmt instanceof Return_
            && $stmt->expr instanceof Array_;
    }
}