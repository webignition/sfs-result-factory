<?php

namespace webignition\SfsResultModels\Tests;

use PHPUnit\Framework\TestCase;
use webignition\SfsResultFactory\InvalidTypeException;

class InvalidTypeExceptionTest extends TestCase
{
    public function testCreate()
    {
        $type = 'foo';
        $invalidTypeException = new InvalidTypeException($type);

        $this->assertEquals($type, $invalidTypeException->getType());
    }
}
