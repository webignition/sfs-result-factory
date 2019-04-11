<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\SfsResultModels\Tests;

use PHPUnit\Framework\TestCase;
use webignition\SfsResultFactory\DataExtractor;

class DataExtractorTest extends TestCase
{
    /**
     * @var DataExtractor
     */
    private $dataExtractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataExtractor = new DataExtractor();
    }

    /**
     * @dataProvider invalidNonNegativeIntegerDataProvider
     */
    public function testGetAsnReturnsNull($value)
    {
        $data = [];
        if ($value !== INF) {
            $data['asn'] = $value;
        }

        $this->assertNull($this->dataExtractor->getAsn($data));
    }

    /**
     * @dataProvider validNonNegativeIntegerDataProvider
     */
    public function testGetAsnSuccess(int $value)
    {
        $this->assertEquals($value, $this->dataExtractor->getAsn([
            'asn' => $value,
        ]));
    }

    /**
     * @dataProvider invalidNonNegativeIntegerDataProvider
     */
    public function testGetFrequencyReturnsNull($value)
    {
        $data = [];
        if ($value !== INF) {
            $data['frequency'] = $value;
        }

        $this->assertNull($this->dataExtractor->getFrequency($data));
    }

    /**
     * @dataProvider validNonNegativeIntegerDataProvider
     */
    public function testGetFrequencySuccess(int $value)
    {
        $this->assertEquals($value, $this->dataExtractor->getFrequency([
            'frequency' => $value,
        ]));
    }

    /**
     * @dataProvider getAppearsReturnsNullDataProvider
     */
    public function testGetAppearsReturnsNull(array $data)
    {
        $this->assertNull($this->dataExtractor->getAppears($data));
    }

    public function getAppearsReturnsNullDataProvider(): array
    {
        return [
            'missing' => [
                'data' => [],
            ],
            'not an integer (numeric)' => [
                'data' => [
                    'appears' => '1',
                ],
            ],
            'not an integer (string)' => [
                'data' => [
                    'appears' => 'foo',
                ],
            ],
            'negative' => [
                'data' => [
                    'appears' => -1
                ],
            ],
        ];
    }

    public function testGetAppearsSuccess()
    {
        $this->assertFalse($this->dataExtractor->getAppears(['appears' => 0]));
        $this->assertTrue($this->dataExtractor->getAppears(['appears' => 1]));
    }

    /**
     * @dataProvider getLastSeenDataProvider
     */
    public function testGetLastSeen(array $data, ?\DateTime $expectedLastSeen)
    {
        $this->assertEquals($expectedLastSeen, $this->dataExtractor->getLastSeen($data));
    }

    public function getLastSeenDataProvider(): array
    {
        return [
            'missing' => [
                'data' => [],
                'expectedLastSeen' => null,
            ],
            'not a date/time string' => [
                'data' => [
                    'lastseen' => 'foo',
                ],
                'expectedLastSeen' => null,
            ],
            'valid' => [
                'data' => [
                    'lastseen' => '2019-04-10 16:26:26',
                ],
                'expectedLastSeen' => new \DateTime('2019-04-10 16:26:26'),
            ],
        ];
    }

    /**
     * @dataProvider getIsBlacklistedDataProvider
     */
    public function testGetIsBlacklisted(array $data, bool $expectedIsBlacklisted)
    {
        $this->assertEquals($expectedIsBlacklisted, $this->dataExtractor->getIsBlacklisted($data));
    }

    public function getIsBlacklistedDataProvider(): array
    {
        return [
            'no lastseen' => [
                'data' => [],
                'expectedIsBlacklisted' => false,
            ],
            'has lastseen, no frequency' => [
                'data' => [
                    'lastseen' => '2019-04-10 16:26:26'
                ],
                'expectedIsBlacklisted' => false,
            ],
            'has lastseen, non-255 frequency' => [
                'data' => [
                    'lastseen' => '2019-04-10 16:26:26',
                    'frequency' => 1,
                ],
                'expectedIsBlacklisted' => false,
            ],
            'lastseen not in threshold' => [
                'data' => [
                    'lastseen' => (new \DateTime('-11 minute'))->format('Y-m-d H:i:s'),
                    'frequency' => 255,
                ],
                'expectedIsBlacklisted' => false,
            ],
            'lastseen on lower threshold' => [
                'data' => [
                    'lastseen' => (new \DateTime('-10 minute'))->format('Y-m-d H:i:s'),
                    'frequency' => 255,
                ],
                'expectedIsBlacklisted' => false,
            ],
            'lastseen just in lower threshold' => [
                'data' => [
                    'lastseen' => (new \DateTime('-9 minute'))->format('Y-m-d H:i:s'),
                    'frequency' => 255,
                ],
                'expectedIsBlacklisted' => true,
            ],
            'lastseen is now' => [
                'data' => [
                    'lastseen' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'frequency' => 255,
                ],
                'expectedIsBlacklisted' => true,
            ],
        ];
    }

    /**
     * @dataProvider getConfidenceDataProvider
     */
    public function testGetConfidence(array $data, ?float $expectedConfidence)
    {
        $this->assertEquals($expectedConfidence, $this->dataExtractor->getConfidence($data));
    }

    public function getConfidenceDataProvider(): array
    {
        return [
            'missing' => [
                'data' => [],
                'expectedConfidence' => null,
            ],
            'not a float (numeric)' => [
                'data' => [
                    'confidence' => '3.14',
                ],
                'expectedConfidence' => 3.14,
            ],
            'not an integer (string)' => [
                'data' => [
                    'confidence' => 'foo',
                ],
                'expectedConfidence' => null,
            ],
            'negative' => [
                'data' => [
                    'confidence' => -1
                ],
                'expectedConfidence' => -1,
            ],
            'zero' => [
                'data' => [
                    'confidence' => 0
                ],
                'expectedConfidence' => 0,
            ],
            'positive' => [
                'data' => [
                    'confidence' => 6.28
                ],
                'expectedConfidence' => 6.28,
            ],
        ];
    }

    /**
     * @dataProvider invalidNonEmptyStringDataProvider
     */
    public function testGetDelegatedCountryCodeReturnsNull($value)
    {
        $data = [];
        if ($value !== INF) {
            $data['delegated'] = $value;
        }

        $this->assertNull($this->dataExtractor->getDelegatedCountryCode($data));
    }

    public function testGetDelegatedCountryCodeSuccess()
    {
        $delegatedCountryCode = 'gb';

        $this->assertEquals(
            $delegatedCountryCode,
            $this->dataExtractor->getDelegatedCountryCode(['delegated' => $delegatedCountryCode])
        );
    }

    /**
     * @dataProvider invalidNonEmptyStringDataProvider
     */
    public function testGetCountryCodeReturnsNull($value)
    {
        $data = [];
        if ($value !== INF) {
            $data['country'] = $value;
        }

        $this->assertNull($this->dataExtractor->getCountryCode($data));
    }

    public function testGetCountryCodeSuccess()
    {
        $countryCode = 'gb';

        $this->assertEquals(
            $countryCode,
            $this->dataExtractor->getCountryCode(['country' => $countryCode])
        );
    }

    public function invalidNonNegativeIntegerDataProvider(): array
    {
        return [
            'not present' => [
                'value' => INF,
            ],
            'null' => [
                'value' => null,
            ],
            'not an integer (numeric)' => [
                'data' => [
                    'frequency' => '1',
                ],
            ],
            'not an integer (string)' => [
                'data' => [
                    'frequency' => 'foo',
                ],
            ],
            'negative' => [
                'data' => [
                    'frequency' => -1
                ],
            ],
        ];
    }

    public function validNonNegativeIntegerDataProvider(): array
    {
        return [
            'zero' => [
                'value' => 0,
            ],
            'positive' => [
                'value' => 10,
            ],
        ];
    }

    public function invalidNonEmptyStringDataProvider(): array
    {
        return [
            'missing' => [
                'value' => INF,
            ],
            'integer' => [
                'value' => 100,
            ],
            'empty string' => [
                'value' => '',
            ],
        ];
    }
}
