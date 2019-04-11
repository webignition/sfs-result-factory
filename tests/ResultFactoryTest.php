<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

namespace webignition\SfsResultModels\Tests;

use PHPUnit\Framework\TestCase;
use webignition\SfsResultFactory\InvalidTypeException;
use webignition\SfsResultFactory\MissingDataException;
use webignition\SfsResultFactory\MissingValueException;
use webignition\SfsResultFactory\ResultFactory;
use webignition\SfsResultInterfaces\ResultInterface;
use webignition\SfsResultModels\Result;

class ResultFactoryTest extends TestCase
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resultFactory = ResultFactory::createFactory();
    }

    public function testCreateFactory()
    {
        $factory = ResultFactory::createFactory();

        $this->assertInstanceOf(ResultFactory::class, $factory);
    }

    public function testHandles()
    {
        $this->assertTrue($this->resultFactory->handlesType(ResultInterface::TYPE_EMAIL));
        $this->assertTrue($this->resultFactory->handlesType(ResultInterface::TYPE_EMAIL_HASH));
        $this->assertTrue($this->resultFactory->handlesType(ResultInterface::TYPE_IP));
        $this->assertTrue($this->resultFactory->handlesType(ResultInterface::TYPE_USERNAME));
    }

    public function testCreateInvalidTypeException()
    {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid type "foo"');

        $this->resultFactory->create([], 'foo');
    }

    public function testCreateMissingValueException()
    {
        $this->expectException(MissingValueException::class);
        $this->expectExceptionMessage('Value missing. Pass in the data array or as the value argument.');

        $this->resultFactory->create([], ResultInterface::TYPE_IP);
    }

    public function testCreateMissingFrequency()
    {
        $this->expectException(MissingDataException::class);
        $this->expectExceptionMessage('Data field "frequency" missing');

        $this->resultFactory->create(
            [
                'value' => '127.0.0.1',
            ],
            ResultInterface::TYPE_IP
        );
    }

    public function testCreateMissingAppears()
    {
        $this->expectException(MissingDataException::class);
        $this->expectExceptionMessage('Data field "appears" missing');

        $this->resultFactory->create(
            [
                'value' => '127.0.0.1',
                'frequency' => 0,
            ],
            ResultInterface::TYPE_IP
        );
    }

    /**
     * @dataProvider createValueDataProvider
     */
    public function testCreateValue(
        array $data,
        string $value,
        string $expectedValue
    ) {
        $result = $this->resultFactory->create($data, ResultInterface::TYPE_IP, $value);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expectedValue, $result->getValue());
    }

    public function createValueDataProvider(): array
    {
        return [
            'value in data' => [
                'data' => [
                    'value' => '127.0.0.1',
                    'frequency' => 0,
                    'appears' => 0,
                    'asn' => 1273,
                ],
                'value' => '',
                'expectedValue' => '127.0.0.1',
            ],
            'value not in data' => [
                'data' => [
                    'frequency' => 0,
                    'appears' => 0,
                    'asn' => 1250,
                ],
                'value' => '10.0.0.1',
                'expectedValue' => '10.0.0.1',
            ],
        ];
    }

    /**
     * @dataProvider createSuccessDataProvider
     */
    public function testCreateSuccess(
        array $data,
        string $type,
        string $expectedType,
        int $expectedFrequency,
        bool $expectedAppears,
        string $expectedValue,
        ?\DateTime $expectedLastSeen,
        ?float $expectedConfidence,
        ?string $expectedDelegatedCountryCode,
        ?string $expectedCountryCode,
        ?int $expectedAsn
    ) {
        $result = $this->resultFactory->create($data, $type);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expectedType, $result->getType());
        $this->assertEquals($expectedFrequency, $result->getFrequency());
        $this->assertEquals($expectedAppears, $result->getAppears());
        $this->assertEquals($expectedValue, $result->getValue());
        $this->assertEquals($expectedLastSeen, $result->getLastSeen());
        $this->assertEquals($expectedConfidence, $result->getConfidence());
        $this->assertEquals($expectedDelegatedCountryCode, $result->getDelegatedCountryCode());
        $this->assertEquals($expectedCountryCode, $result->getCountryCode());
        $this->assertEquals($expectedAsn, $result->getAsn());
    }

    public function createSuccessDataProvider(): array
    {
        return [
            'email, unseen' => [
                'data' => [
                    'value' => 'user@example.com',
                    'frequency' => 0,
                    'appears' => 0,
                ],
                'type' => ResultInterface::TYPE_EMAIL,
                'expectedType' => ResultInterface::TYPE_EMAIL,
                'expectedFrequency' => 0,
                'expectedAppears' => false,
                'expectedValue' => 'user@example.com',
                'expectedLastSeen' => null,
                'expectedConfidence' => null,
                'expectedDelegatedCountryCode' => null,
                'expectedCountryCode' => null,
                'expectedAsn' => null,
            ],
            'email, seen' => [
                'data' => [
                    'value' => 'user@example.com',
                    'lastseen' => '2019-04-10 16:26:26',
                    'frequency' => 11,
                    'appears' => 1,
                    'confidence' => 80.2
                ],
                'type' => ResultInterface::TYPE_EMAIL,
                'expectedType' => ResultInterface::TYPE_EMAIL,
                'expectedFrequency' => 11,
                'expectedAppears' => true,
                'expectedValue' => 'user@example.com',
                'expectedLastSeen' => new \DateTime('2019-04-10 16:26:26'),
                'expectedConfidence' => 80.2,
                'expectedDelegatedCountryCode' => null,
                'expectedCountryCode' => null,
                'expectedAsn' => null,
            ],
            'email hash, unseen' => [
                'data' => [
                    'value' => 'A46D59F5F2114760BCCD2795223F6D37',
                    'frequency' => 0,
                    'appears' => 0,
                ],
                'type' => ResultInterface::TYPE_EMAIL_HASH,
                'expectedType' => ResultInterface::TYPE_EMAIL_HASH,
                'expectedFrequency' => 0,
                'expectedAppears' => false,
                'expectedValue' => 'A46D59F5F2114760BCCD2795223F6D37',
                'expectedLastSeen' => null,
                'expectedConfidence' => null,
                'expectedDelegatedCountryCode' => null,
                'expectedCountryCode' => null,
                'expectedAsn' => null,
            ],
            'email hash, seen' => [
                'data' => [
                    'value' => 'A46D59F5F2114760BCCD2795223F6D37',
                    'lastseen' => '2019-04-10 16:26:26',
                    'frequency' => 11,
                    'appears' => 1,
                    'confidence' => 80.2
                ],
                'type' => ResultInterface::TYPE_EMAIL_HASH,
                'expectedType' => ResultInterface::TYPE_EMAIL_HASH,
                'expectedFrequency' => 11,
                'expectedAppears' => true,
                'expectedValue' => 'A46D59F5F2114760BCCD2795223F6D37',
                'expectedLastSeen' => new \DateTime('2019-04-10 16:26:26'),
                'expectedConfidence' => 80.2,
                'expectedDelegatedCountryCode' => null,
                'expectedCountryCode' => null,
                'expectedAsn' => null,
            ],
            'ip, unseen' => [
                'data' => [
                    'value' => '127.0.0.1',
                    'frequency' => 0,
                    'appears' => 0,
                    'asn' => 1273,
                ],
                'type' => ResultInterface::TYPE_IP,
                'expectedType' => ResultInterface::TYPE_IP,
                'expectedFrequency' => 0,
                'expectedAppears' => false,
                'expectedValue' => '127.0.0.1',
                'expectedLastSeen' => null,
                'expectedConfidence' => null,
                'expectedDelegatedCountryCode' => null,
                'expectedCountryCode' => null,
                'expectedAsn' => 1273,
            ],
            'ip, seen' => [
                'data' => [
                    'value' => '255.255.255.255',
                    'lastseen' => '2019-04-10 16:26:26',
                    'frequency' => 10,
                    'appears' => 1,
                    'confidence' => 99.5,
                    'delegated' => 'fr',
                    'country' => 'gb',
                    'asn' => 789,
                ],
                'type' => ResultInterface::TYPE_IP,
                'expectedType' => ResultInterface::TYPE_IP,
                'expectedFrequency' => 10,
                'expectedAppears' => true,
                'expectedValue' => '255.255.255.255',
                'expectedLastSeen' => new \DateTime('2019-04-10 16:26:26'),
                'expectedConfidence' => 99.5,
                'expectedDelegatedCountryCode' => 'fr',
                'expectedCountryCode' => 'gb',
                'expectedAsn' => 789,
            ],
            'username, unseen' => [
                'data' => [
                    'value' => 'foo',
                    'frequency' => 0,
                    'appears' => 0,
                ],
                'type' => ResultInterface::TYPE_USERNAME,
                'expectedType' => ResultInterface::TYPE_USERNAME,
                'expectedFrequency' => 0,
                'expectedAppears' => false,
                'expectedValue' => 'foo',
                'expectedLastSeen' => null,
                'expectedConfidence' => null,
                'expectedDelegatedCountryCode' => null,
                'expectedCountryCode' => null,
                'expectedAsn' => null,
            ],
            'username, seen' => [
                'data' => [
                    'value' => 'foo',
                    'lastseen' => '2019-04-10 16:26:26',
                    'frequency' => 11,
                    'appears' => 1,
                    'confidence' => 80.2
                ],
                'type' => ResultInterface::TYPE_USERNAME,
                'expectedType' => ResultInterface::TYPE_USERNAME,
                'expectedFrequency' => 11,
                'expectedAppears' => true,
                'expectedValue' => 'foo',
                'expectedLastSeen' => new \DateTime('2019-04-10 16:26:26'),
                'expectedConfidence' => 80.2,
                'expectedDelegatedCountryCode' => null,
                'expectedCountryCode' => null,
                'expectedAsn' => null,
            ],
        ];
    }
}
