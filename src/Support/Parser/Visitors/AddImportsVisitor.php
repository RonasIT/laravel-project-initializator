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

    public function enterNode(Node $node): void
    {
        if (!$node instanceof Namespace_) {
            return;
        }

        $existingImports = $this->getExistingImports($node);

        $importsToAdd = array_diff($this->classFullNames, $existingImports);

        if (empty($importsToAdd)) {
            return;
        }

        $newUseStmts = $this->getNewImports($importsToAdd);

        $inserted = false;

        $this->insertImportsToUseBlock($node, $newUseStmts, $inserted);

        if (!$inserted) {
            foreach ($newUseStmts as $useStmt) {
                $node->stmts[] = $useStmt;
            }
        }
    }

    protected function getExistingImports(Node $node): array
    {
        $existingUses = [];

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Use_) {
                foreach ($stmt->uses as $use) {
                    $existingUses[] = $use->name->toString();
                }
            }
        }

        return $existingUses;
    }

    protected function getNewImports(array $importsToAdd): array
    {
        $newUseStmts = [];

        foreach ($importsToAdd as $classFullName) {
            $newUseStmts[] = new Use_([
                new UseUse(new Name($classFullName))
            ]);
        }

        return $newUseStmts;
    }

    protected function insertImportsToUseBlock(Node $node, array $newUseStmts, bool &$inserted): void
    {
        foreach ($node->stmts as $i => $stmt) {
            if (!($stmt instanceof Use_)) {
                array_splice($node->stmts, $i, 0, $newUseStmts);

                $inserted = true;

                break;
            }
        }
    }
}