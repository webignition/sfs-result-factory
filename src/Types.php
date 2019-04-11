<?php

namespace webignition\SfsResultFactory;

use webignition\SfsResultInterfaces\ResultInterface;

class Types
{
    const VALID_TYPES = [
        ResultInterface::TYPE_EMAIL,
        ResultInterface::TYPE_EMAIL_HASH,
        ResultInterface::TYPE_IP,
        ResultInterface::TYPE_USERNAME,
    ];
}
