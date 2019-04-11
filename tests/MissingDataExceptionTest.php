<?php

namespace webignition\SfsResultModels\Tests;

use PHPUnit\Framework\TestCase;
use webignition\SfsResultFactory\MissingDataException;

class MissingDataExceptionTest extends TestCase
{
    public function testCreate()
    {
        $field = 'foo';
        $missingDataException = new MissingDataException($field);

        $this->assertEquals($field, $missingDataException->getField());
    }
}
