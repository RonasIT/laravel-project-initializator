<?php

namespace RonasIT\ProjectInitializator\Support;

use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use RonasIT\ProjectInitializator\Support\Visitors\ArrayVisitors\RemoveValueFromArrayPropertyVisitor;
use RonasIT\ProjectInitializator\Support\Visitors\ArrayVisitors\RemoveValueFromMethodReturnArrayVisitor;
use RonasIT\ProjectInitializator\Support\Visitors\ArrayVisitors\AddValueToArrayPropertyVisitor;
use PhpParser\Node\Stmt\Expression;
use RonasIT\ProjectInitializator\Support\Visitors\MethodVisitors\AppendPartToMethodVisitor;
use RonasIT\ProjectInitializator\Support\Visitors\AddImportsVisitor;

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
    }

    public function removeValueFromArrayProperty(array $propertyNames, string $value): self
    {
        $this->traverser->addVisitor(new RemoveValueFromArrayPropertyVisitor($propertyNames, $value));

        return $this;
    }

    public function removeValueFromMethodReturnArray(array $methodNames, string $value): self
    {
        $this->traverser->addVisitor(new RemoveValueFromMethodReturnArrayVisitor($methodNames, $value));

        return $this;
    }

    public function addValueToArrayProperty(array $propertyNames, string $value): self
    {
        $this->traverser->addVisitor(new AddValueToArrayPropertyVisitor($propertyNames, $value));

        return $this;
    }

    public function appendPartToMethod(string $methodName, Expression $expression): self
    {
        $this->traverser->addVisitor(new AppendPartToMethodVisitor($methodName, $expression));

        return $this;
    }

    public function addImports(array $fullClassNames): self
    {
        $this->traverser->addVisitor(new AddImportsVisitor($fullClassNames));

        return $this;
    }

    public function save(): void
    {
        $modifiedAst = $this->traverser->traverse($this->ast);
        file_put_contents($this->filePath, $this->printer->prettyPrintFile($modifiedAst));
    }
}