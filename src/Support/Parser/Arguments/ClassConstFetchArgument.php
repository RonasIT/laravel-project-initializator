<?php

namespace RonasIT\ProjectInitializator\Support\Parser\Arguments;

use PhpParser\Node\Arg;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\ClassConstFetch;

class ClassConstFetchArgument extends Arg
{
    public function __construct(
       protected string $argumentName,
    )
    {
        $this->value = new ClassConstFetch(new Name($this->argumentName), 'class');

        return parent::__construct($this->value);
    }
}