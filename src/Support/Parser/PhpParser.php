<?php

namespace RonasIT\ProjectInitializator\Support\Parser;

use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use RonasIT\ProjectInitializator\Support\Parser\Visitors\ArrayVisitors\PropertyArrayVisitors\RemoveValueFromArrayPropertyPropertyArrayVisitor;
use RonasIT\ProjectInitializator\Support\Parser\Visitors\ArrayVisitors\MethodReturnArrayVisitors\RemoveValueFromMethodReturnArrayVisitor;
use RonasIT\ProjectInitializator\Support\Parser\Visitors\ArrayVisitors\PropertyArrayVisitors\AddValueToArrayPropertyPropertyArrayVisitor;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Use_;

class PhpParser
{
    private array $ast;
    private NodeTraverser $traverser;
    private Standard $printer;

    public function __construct(protected string $filePath)
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $this->ast = $parser->parse(file_get_contents($filePath));

        $this->traverser = new NodeTraverser();
        $this->printer = new Standard();

        $this->addEmptySpacesVisitor();
    }

    public function removeValueFromArrayProperty(array $propertyNames, string $value): self
    {
        $this->traverser->addVisitor(new RemoveValueFromArrayPropertyPropertyArrayVisitor($propertyNames, $value));

        return $this;
    }

    public function removeValueFromMethodReturnArray(array $methodNames, string $value): self
    {
        $this->traverser->addVisitor(new RemoveValueFromMethodReturnArrayVisitor($methodNames, $value));

        return $this;
    }

    public function addValueToArrayProperty(array $propertyNames, string $value): self
    {
        $this->traverser->addVisitor(new AddValueToArrayPropertyPropertyArrayVisitor($propertyNames, $value));

        return $this;
    }
    
    public function save(): void
    {
        $modifiedAst = $this->traverser->traverse($this->ast);
        file_put_contents($this->filePath, $this->printer->prettyPrintFile($modifiedAst));
    }

    protected function addEmptySpacesVisitor(): void
    {
        $this->traverser->addVisitor(
            new class extends NodeVisitorAbstract {
                public function enterNode(Node $node): void
                {
                    if (!$node instanceof Namespace_) {
                        return;
                    }

                    $this->addLineAfterLastUse($node);
                    $this->addEndLine($node);
                }

                private function addLineAfterLastUse(Namespace_ $namespace): void
                {
                    $lastUseIndex = $this->findLastUseIndex($namespace);

                    if ($lastUseIndex === null) {
                        return;
                    }

                    $afterLastUse = $namespace->stmts[$lastUseIndex + 1] ?? null;

                    if (!$afterLastUse instanceof Nop) {
                        array_splice($namespace->stmts, $lastUseIndex + 1, 0, [new Nop()]);
                    }
                }

                private function addEndLine(Namespace_ $namespace): void
                {
                    $lastStmtIndex = count($namespace->stmts) - 1;

                    if ($lastStmtIndex < 0) {
                        return;
                    }

                    if (!$namespace->stmts[$lastStmtIndex] instanceof Nop) {
                        $namespace->stmts[] = new Nop();
                    }
                }

                private function findLastUseIndex(Namespace_ $namespace): ?int
                {
                    $lastUseIndex = null;

                    foreach ($namespace->stmts as $i => $stmt) {
                        if ($stmt instanceof Use_) {
                            $lastUseIndex = $i;
                        }
                    }

                    return $lastUseIndex;
                }
            }
        );
    }
}