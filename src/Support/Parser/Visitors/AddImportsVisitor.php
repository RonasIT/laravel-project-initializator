<?php

namespace RonasIT\ProjectInitializator\Support\Parser\Visitors;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\Name;

class AddImportsVisitor extends NodeVisitorAbstract
{
    public function __construct(
        protected array $classFullNames,
    )
    {
    }

    public function enterNode(Node $node)
    {
        if (!$node instanceof Namespace_) {
            return null;
        }

        $existingUses = [];
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Use_) {
                foreach ($stmt->uses as $use) {
                    $existingUses[] = $use->name->toString();
                }
            }
        }

        $toAdd = array_diff($this->classFullNames, $existingUses);
        if (empty($toAdd)) {
            return null;
        }

        $newUseStmts = [];
        foreach ($toAdd as $classFullName) {
            $newUseStmts[] = new Use_([
                new UseUse(new Name($classFullName))
            ]);
        }

        $inserted = false;
        foreach ($node->stmts as $i => $stmt) {
            if (!($stmt instanceof Use_)) {
                array_splice($node->stmts, $i, 0, $newUseStmts);
                $inserted = true;
                break;
            }
        }

        if (!$inserted) {
            foreach ($newUseStmts as $useStmt) {
                $node->stmts[] = $useStmt;
            }
        }

        return null;
    }
}