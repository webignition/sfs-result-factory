<?php

namespace webignition\SfsResultFactory;

class MissingValueException extends \Exception
{
    public function __construct()
    {
        parent::__construct(
            'Value missing. Pass in the data array or as the value argument.'
        );
    }
}
