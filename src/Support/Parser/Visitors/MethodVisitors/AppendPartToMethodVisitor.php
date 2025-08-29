<?php

namespace RonasIT\ProjectInitializator\Support\Parser\Visitors\MethodVisitors;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use RonasIT\ProjectInitializator\Support\Parser\Expressions\AppendMethodCall;
use PhpParser\Node\Arg;

class AppendPartToMethodVisitor extends NodeVisitorAbstract
{
    protected array $arguments;

    public function __construct(
        protected string $methodName,
        protected string $variableName,
        protected string $callMethodName,
        protected ?string $propertyName = null,
        Arg ...$arguments,
    )
    {
        $this->arguments = $arguments;
    }

    public function enterNode(Node $node)
    {
        $stmtToAdd = new Expression(
            AppendMethodCall::make(
                variableName: $this->variableName,
                methodName: $this->callMethodName,
                arguments: $this->arguments,
                propertyName: $this->propertyName,
            )
        );

        if ($node instanceof ClassMethod
            && $node->name->toString() === $this->methodName
            && !in_array($this->classMethodKeys($stmtToAdd), $this->findMethodKeysInExistClassMethods($node))) {
            $node->stmts[] = $stmtToAdd;
        }
    }

    protected function classMethodKeys($stmts): array
    {
        $expressionAttributes = [];

        $expressionAttributes[] = $stmts->expr->name->name;

        foreach ($stmts->expr->var as $var) {
            $expressionAttributes[] = $var->name;
        }

        foreach ($stmts->expr->args as $arg) {
            $expressionAttributes[] = $arg->value->class->name;
        }

        return $expressionAttributes;
    }

    protected function findMethodKeysInExistClassMethods(Node $node): array
    {
        $nodeStmtsKey = [];

        foreach ($node->stmts as $stmt) {
            $nodeStmtsKey[] = $this->classMethodKeys($stmt);
        }

        return $nodeStmtsKey;
    }
}