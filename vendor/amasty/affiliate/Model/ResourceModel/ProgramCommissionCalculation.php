<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\ResourceModel;

use Amasty\Affiliate\Model\EntityValidation\EntityValidationInterface;
use Amasty\Affiliate\Model\EntityValidatorsProvider;
use Amasty\Affiliate\Model\ProgramCommissionCalculation as ProgramCommissionCalculationModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class ProgramCommissionCalculation extends AbstractDb
{
    public const TABLE_NAME = 'amasty_affiliate_commission_calculation';

    /**
     * @var array
     */
    protected $_serializableFields = [
        ProgramCommissionCalculationModel::SKUS => [[], []],
        ProgramCommissionCalculationModel::CATEGORIES => [[], []]
    ];

    /**
     * @var EntityValidatorsProvider
     */
    private $validatorsProvider;

    public function __construct(
        Context $context,
        EntityValidatorsProvider $validatorsProvider,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->validatorsProvider = $validatorsProvider;
    }

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ProgramCommissionCalculationModel::ID);
    }

    /**
     * @return \Zend_Validate|null
     */
    public function getValidationRulesBeforeSave()
    {
        return $this->validatorsProvider->get(ProgramCommissionCalculationModel::class);
    }
}
