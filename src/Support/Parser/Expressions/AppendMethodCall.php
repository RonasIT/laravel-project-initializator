<?php

namespace RonasIT\ProjectInitializator\Support\Parser\Expressions;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;

class AppendMethodCall extends MethodCall
{
    public static function make(
        string $variableName,
        string $methodName,
        array $arguments,
        ?string $propertyName = null,
        array $attributes = [],
    ): self
    {
        $variable = self::setVariable($variableName, $propertyName);

       return new self(
           var: $variable,
           name: $methodName,
           args: $arguments,
           attributes: $attributes
       );
    }

    protected static function setVariable(string $variableName, ?string $propertyName = null): PropertyFetch|Variable
    {
        return !empty($propertyName)
            ? new PropertyFetch(new Variable($variableName), $propertyName)
            : new Variable($variableName);
    }
}