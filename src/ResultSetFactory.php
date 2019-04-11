<?php

namespace webignition\SfsResultFactory;

use webignition\SfsResultInterfaces\ResultSetInterface;
use webignition\SfsResultModels\ResultSet;

class ResultSetFactory
{
    private $resultFactory;

    public function __construct(ResultFactory $resultFactory = null)
    {
        $this->resultFactory = $resultFactory ?? ResultFactory::createFactory();
    }

    public function create(array $data): ResultSetInterface
    {
        $resultSet = new ResultSet();

        foreach (Types::VALID_TYPES as $type) {
            $resultTypeData = $data[$type] ?? [];

            foreach ($resultTypeData as $resultData) {
                try {
                    $resultSet->addResult($this->resultFactory->create($resultData, $type));
                } catch (InvalidTypeException $e) {
                } catch (MissingDataException $e) {
                } catch (MissingValueException $e) {
                }
            }
        }

        return $resultSet;
    }
}
