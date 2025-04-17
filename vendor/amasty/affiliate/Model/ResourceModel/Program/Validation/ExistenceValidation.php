<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\ResourceModel\Program\Validation;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;

class ExistenceValidation implements \Zend_Validate_Interface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Phrase
     */
    private $errorMessage;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $idField;

    /**
     * @var string
     */
    private $entityField;

    /**
     * @var string
     */
    private $errorText;

    public function __construct(
        ResourceConnection $resourceConnection,
        array $config
    ) {
        $this->resourceConnection = $resourceConnection;

        $this->validateConfig($config);
        $this->tableName = $config['tableName'];
        $this->idField = $config['idField'];
        $this->entityField = $config['entityField'];
        $this->errorText = $config['errorText'];
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid($value): bool
    {
        if (!($idsToValidate = $this->getValue($value))) {
            return true;
        }

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            $this->resourceConnection->getTableName($this->tableName),
            $this->idField
        )->where(
            $this->idField . ' IN (?)',
            $idsToValidate
        );
        $availableIds = $connection->fetchCol($select);
        $invalidIds = array_diff($idsToValidate, $availableIds);

        if ($invalidIds) {
            $this->errorMessage = __(
                $this->errorText,
                implode(', ', $invalidIds)
            );

            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return [$this->errorMessage];
    }

    /**
     * @param AbstractModel $entity
     * @return array|mixed|null
     */
    private function getValue(AbstractModel $entity)
    {
        return $entity->getData($this->entityField);
    }

    /**
     * @param array $config
     */
    private function validateConfig(array $config)
    {
        if (!isset($config['tableName'])) {
            throw new \LogicException('\'tableName\' must be specified.');
        }
        if (!isset($config['idField'])) {
            throw new \LogicException('\'idField\' must be specified.');
        }
        if (!isset($config['entityField'])) {
            throw new \LogicException('\'entityField\' must be specified.');
        }
        if (!isset($config['errorText'])) {
            throw new \LogicException('\'errorText\' must be specified.');
        }
    }
}
