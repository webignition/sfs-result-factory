<?php

namespace webignition\SfsResultFactory;

use webignition\SfsResultInterfaces\ResultInterface;
use webignition\SfsResultModels\Result;

class IpResultFactory implements ResultFactoryInterface
{
    private $dataExtractor;

    public function __construct(DataExtractor $dataExtractor)
    {
        $this->dataExtractor = $dataExtractor;
    }

    public function handlesType(string $type): bool
    {
        return $type === ResultInterface::TYPE_IP;
    }

    public function create(array $data, string $type, string $value = null): ResultInterface
    {
        return new Result(
            $data['value'] ?? $value,
            $type,
            $this->dataExtractor->getFrequency($data) ?? 0,
            $this->dataExtractor->getAppears($data) ?? false,
            $this->dataExtractor->getIsBlacklisted($data),
            $this->dataExtractor->getLastSeen($data),
            $this->dataExtractor->getConfidence($data),
            $this->dataExtractor->getDelegatedCountryCode($data),
            $this->dataExtractor->getCountryCode($data),
            $this->dataExtractor->getAsn($data),
            $this->dataExtractor->getIsTorExit($data)
        );
    }
}
