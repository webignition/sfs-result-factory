<?php

namespace webignition\SfsResultFactory;

class MissingDataException extends \Exception
{
    private $field;

    public function __construct(string $field)
    {
        parent::__construct(
            'Data field "' . $field . '" missing'
        );

        $this->field = $field;
    }

    public function getField(): string
    {
        return $this->field;
    }
}
