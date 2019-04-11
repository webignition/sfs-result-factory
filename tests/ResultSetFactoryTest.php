<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\SfsResultFactory\Tests;

use PHPUnit\Framework\TestCase;
use webignition\SfsResultFactory\DataExtractor;
use webignition\SfsResultFactory\ResultFactory;
use webignition\SfsResultFactory\ResultSetFactory;
use webignition\SfsResultInterfaces\ResultSetInterface;

class ResultSetFactoryTest extends TestCase
{
    /**
     * @var ResultSetFactory
     */
    private $resultSetFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resultSetFactory = new ResultSetFactory();
    }

    public function testCreateFactoryThrowsMissingValueException()
    {
        $resultSet = $this->resultSetFactory->create([
            'success' => 1,
            'email' => [
                [],
            ],
        ]);

        $this->assertInstanceOf(ResultSetInterface::class, $resultSet);
        $this->assertEmpty($resultSet);
    }

    public function testCreateFactoryThrowsInvalidTypeException()
    {
        $resultFactory = new ResultFactory(new DataExtractor(), []);

        $resultSetFactory = new ResultSetFactory($resultFactory);

        $resultSet = $resultSetFactory->create([
            'success' => 1,
            'email' => [
                [
                    'value' => 'user1@example.com',
                    'frequency' => 0,
                    'appears' => 0,
                ],
            ],
        ]);

        $this->assertInstanceOf(ResultSetInterface::class, $resultSet);
        $this->assertEmpty($resultSet);
    }

    public function testCreateFactoryThrowsMissingDataException()
    {
        $resultSet = $this->resultSetFactory->create([
            'success' => 1,
            'email' => [
                [
                    'value' => 'user1@example.com',
                ],
            ],
        ]);

        $this->assertInstanceOf(ResultSetInterface::class, $resultSet);
        $this->assertEmpty($resultSet);
    }

    /**
     * @dataProvider createReturnsEmptySetDataProvider
     */
    public function testCreateReturnsEmptySet(array $data)
    {
        $resultSet = $this->resultSetFactory->create($data);

        $this->assertInstanceOf(ResultSetInterface::class, $resultSet);
        $this->assertEmpty($resultSet);
    }

    public function createReturnsEmptySetDataProvider(): array
    {
        return [
            'no data' => [
                'data' => [],
            ],
            'no results' => [
                'data' => [
                    'success' => 1,
                ],
            ],
            'no valid type keys' => [
                'data' => [
                    'success' => 1,
                    'foo' => [
                        [
                            'value' => '127.0.0.1',
                            'frequency' => 0,
                            'appears' => 0,
                            'asn' => 1273,
                        ],
                    ],
                    'bar' => [
                        [
                            'value' => '255.255.255.255',
                            'lastseen' => '2019-04-10 16:26:26',
                            'frequency' => 10,
                            'appears' => 1,
                            'confidence' => 99.5,
                            'delegated' => 'fr',
                            'country' => 'gb',
                            'asn' => 789,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider createReturnsPopulatedSetDataProvider
     */
    public function testCreateReturnsPopulatedSet(array $data, array $expectedResultSetData)
    {
        $resultSet = $this->resultSetFactory->create($data);

        $this->assertInstanceOf(ResultSetInterface::class, $resultSet);
        $this->assertCount(count($expectedResultSetData), $resultSet);

        foreach ($resultSet as $resultIndex => $result) {
            $expectedResultData = $expectedResultSetData[$resultIndex];

            $this->assertEquals(
                $expectedResultData['value'],
                $result->getValue()
            );
        }
    }

    public function createReturnsPopulatedSetDataProvider(): array
    {
        return [
            'email only' => [
                'data' => [
                    'success' => 1,
                    'email' => [
                        [
                            'value' => 'user1@example.com',
                            'frequency' => 0,
                            'appears' => 0,
                        ],
                        [
                            'value' => 'user2@example.com',
                            'lastseen' => '2019-04-10 16:26:26',
                            'frequency' => 11,
                            'appears' => 1,
                            'confidence' => 80.2
                        ],
                    ],
                ],
                'expectedResultSetData' => [
                    [
                        'value' => 'user1@example.com',
                    ],
                    [
                        'value' => 'user2@example.com',
                    ],
                ],
            ],
            'emailHash only' => [
                'data' => [
                    'success' => 1,
                    'emailHash' => [
                        [
                            'value' => '111d68d06e2d317b5a59c2c6c5bad808',
                            'frequency' => 0,
                            'appears' => 0,
                        ],
                        [
                            'value' => 'ab53a2911ddf9b4817ac01ddcd3d975f',
                            'lastseen' => '2019-04-10 16:26:26',
                            'frequency' => 11,
                            'appears' => 1,
                            'confidence' => 80.2
                        ],
                    ],
                ],
                'expectedResultSetData' => [
                    [
                        'value' => '111d68d06e2d317b5a59c2c6c5bad808',
                    ],
                    [
                        'value' => 'ab53a2911ddf9b4817ac01ddcd3d975f',
                    ],
                ],
            ],
            'ip only' => [
                'data' => [
                    'success' => 1,
                    'ip' => [
                        [
                            'value' => '127.0.0.1',
                            'frequency' => 0,
                            'appears' => 0,
                            'asn' => 1273,
                        ],
                        [
                            'value' => '255.255.255.255',
                            'lastseen' => '2019-04-10 16:26:26',
                            'frequency' => 10,
                            'appears' => 1,
                            'confidence' => 99.5,
                            'delegated' => 'fr',
                            'country' => 'gb',
                            'asn' => 789,
                        ],
                    ],
                ],
                'expectedResultSetData' => [
                    [
                        'value' => '127.0.0.1',
                    ],
                    [
                        'value' => '255.255.255.255',
                    ],
                ],
            ],
            'username only' => [
                'data' => [
                    'success' => 1,
                    'email' => [
                        [
                            'value' => 'user1',
                            'frequency' => 0,
                            'appears' => 0,
                        ],
                        [
                            'value' => 'user2',
                            'lastseen' => '2019-04-10 16:26:26',
                            'frequency' => 11,
                            'appears' => 1,
                            'confidence' => 80.2
                        ],
                    ],
                ],
                'expectedResultSetData' => [
                    [
                        'value' => 'user1',
                    ],
                    [
                        'value' => 'user2',
                    ],
                ],
            ],
            'mixed' => [
                'data' => [
                    'success' => 1,
                    'email' => [
                        [
                            'value' => 'user@example.com',
                            'lastseen' => '2019-04-10 16:26:26',
                            'frequency' => 11,
                            'appears' => 1,
                            'confidence' => 80.2
                        ],
                    ],
                    'emailHash' => [
                        [
                            'value' => '111d68d06e2d317b5a59c2c6c5bad808',
                            'frequency' => 0,
                            'appears' => 0,
                        ],
                        [
                            'value' => 'ab53a2911ddf9b4817ac01ddcd3d975f',
                            'lastseen' => '2019-04-10 16:26:26',
                            'frequency' => 11,
                            'appears' => 1,
                            'confidence' => 80.2
                        ],
                    ],
                    'ip' => [
                        [
                            'value' => '255.255.255.255',
                            'lastseen' => '2019-04-10 16:26:26',
                            'frequency' => 10,
                            'appears' => 1,
                            'confidence' => 99.5,
                            'delegated' => 'fr',
                            'country' => 'gb',
                            'asn' => 789,
                        ],
                    ],
                ],
                'expectedResultSetData' => [
                    [
                        'value' => 'user@example.com',
                    ],
                    [
                        'value' => '111d68d06e2d317b5a59c2c6c5bad808',
                    ],
                    [
                        'value' => 'ab53a2911ddf9b4817ac01ddcd3d975f',
                    ],
                    [
                        'value' => '255.255.255.255',
                    ],
                ],
            ],
        ];
    }
}
