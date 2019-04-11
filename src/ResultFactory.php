<?php

namespace webignition\SfsResultFactory;

use webignition\SfsResultInterfaces\ResultInterface;

class ResultFactory implements ResultFactoryInterface
{
    const VALID_TYPES = [
        ResultInterface::TYPE_EMAIL,
        ResultInterface::TYPE_EMAIL_HASH,
        ResultInterface::TYPE_IP,
        ResultInterface::TYPE_USERNAME,
    ];

    /**
     * @var ResultFactoryInterface[] $factories
     */
    private $factories;

    /**
     * @var DataExtractor
     */
    private $dataExtractor;

    public function __construct(DataExtractor $dataExtractor, array $factories)
    {
        foreach ($factories as $factory) {
            if ($factory instanceof ResultFactoryInterface) {
                $this->factories[] = $factory;
            }
        }

        $this->dataExtractor = $dataExtractor;
    }

    public static function createFactory(): ResultFactory
    {
        $dataExtractor = new DataExtractor();

        return new ResultFactory(
            $dataExtractor,
            [
                new IpResultFactory($dataExtractor),
                new NonIpResultFactory($dataExtractor),
            ]
        );
    }

    /**
     * @param array $data
     * @param string $type
     * @param string|null $value
     *
     * @return ResultInterface
     *
     * @throws InvalidTypeException
     * @throws MissingValueException
     * @throws MissingDataException
     */
    public function create(array $data, string $type, string $value = null): ResultInterface
    {
        $factory = $this->findFactory($type);
        if (null === $factory) {
            throw new InvalidTypeException($type);
        }

        $value = $data['value'] ?? $value;
        if (null === $value) {
            throw new MissingValueException();
        }

        $frequency = $this->dataExtractor->getFrequency($data);
        if (null === $frequency) {
            throw new MissingDataException(DataExtractor::FIELD_FREQUENCY);
        }

        $appears = $this->dataExtractor->getAppears($data);
        if (null === $appears) {
            throw new MissingDataException(DataExtractor::FIELD_APPEARS);
        }

        return $factory->create($data, $type, $value);
    }

    public function handlesType(string $type): bool
    {
        return in_array($type, self::VALID_TYPES);
    }

    private function findFactory(string $type): ?ResultFactoryInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->handlesType($type)) {
                return $factory;
            }
        }

        return null;
    }
}
