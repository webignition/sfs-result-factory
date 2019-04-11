<?php

namespace webignition\SfsResultFactory;

use webignition\SfsResultInterfaces\ResultInterface;

interface ResultFactoryInterface
{
    public function create(array $data, string $type, string $value = null): ResultInterface;
    public function handlesType(string $type): bool;
}
