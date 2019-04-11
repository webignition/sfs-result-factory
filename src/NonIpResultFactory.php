<?php

namespace webignition\SfsResultFactory;

use webignition\SfsResultInterfaces\ResultInterface;
use webignition\SfsResultModels\Result;

class NonIpResultFactory implements ResultFactoryInterface
{
    const VALID_TYPES = [
        ResultInterface::TYPE_EMAIL,
        ResultInterface::TYPE_EMAIL_HASH,
        ResultInterface::TYPE_USERNAME,
    ];

    private $dataExtractor;

    public function __construct(DataExtractor $dataExtractor)
    {
        $this->dataExtractor = $dataExtractor;
    }

    public function handlesType(string $type): bool
    {
        return in_array($type, self::VALID_TYPES);
    }

    /**
     * @param array $data
     * @param string|null $value
     * @param string|null $type
     *
     * @return ResultInterface
     */
    public function create(array $data, string $type, string $value = null): ResultInterface
    {
        return new Result(
            $data['value'] ?? $value,
            $type,
            $this->dataExtractor->getFrequency($data) ?? 0,
            $this->dataExtractor->getAppears($data) ?? false,
            $this->dataExtractor->getIsBlacklisted($data),
            $this->dataExtractor->getLastSeen($data),
            $this->dataExtractor->getConfidence($data)
        );
    }
}
