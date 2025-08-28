<?php

namespace RonasIT\ProjectInitializator\Support\Parser\Visitors\ArrayVisitors;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;

abstract class BaseArrayVisitor extends NodeVisitorAbstract
{
    public function __construct(
        protected array $stmtNames,
        protected string $value,
    )
    {
    }

    protected function isTargetProperty(string $propName, $default): bool
    {
        return in_array($propName, $this->stmtNames, true)
            && $default instanceof Array_;
    }

    protected function getTargetItems(array $items): array
    {
        $filteredArray = array_filter($items, fn($item) => !$this->isTargetItem($item));

        return array_values($filteredArray);
    }

    abstract protected function isTargetItem(?ArrayItem $item): bool;
}