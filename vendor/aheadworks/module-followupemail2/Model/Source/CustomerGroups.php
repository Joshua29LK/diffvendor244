<?php
namespace Aheadworks\Followupemail2\Model\Source;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Convert\DataObject as ConvertDataObject;

/**
 * Class CustomerGroups
 * @package Aheadworks\Followupemail2\Model\Source
 */
class CustomerGroups implements OptionSourceInterface
{
    /**
     * @var ConvertDataObject
     */
    private $objectConverter;

    /**
     * @var array
     */
    private $options;

    /**
     * @var GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @var bool;
     */
    private $isNeedToAddNotLoggedInCustomerGroup;

    /**
     * @param GroupManagementInterface $groupManagement
     * @param ConvertDataObject $objectConverter
     */
    public function __construct(
        GroupManagementInterface $groupManagement,
        ConvertDataObject $objectConverter
    ) {
        $this->groupManagement = $groupManagement;
        $this->objectConverter = $objectConverter;
        $this->isNeedToAddNotLoggedInCustomerGroup = false;
    }

    /**
     * Set parameter to add not logged in customer group or not
     *
     * @param bool $flag
     * @return bool
     */
    public function setIsNeedToAddNotLoggedInCustomerGroup($flag)
    {
        if (!empty($flag)) {
            $this->isNeedToAddNotLoggedInCustomerGroup = $flag;
        }
        return $this->isNeedToAddNotLoggedInCustomerGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $customerGroups = $this->getCustomerGroups();
            $this->options = $this->getOptionArray($customerGroups);
        }

        return $this->options;
    }

    /**
     * Retrieve customer groups
     *
     * @return array|\Magento\Customer\Api\Data\GroupInterface[]
     */
    private function getCustomerGroups()
    {
        $customerGroups = $this->groupManagement->getLoggedInGroups();
        if ($this->isNeedToAddNotLoggedInCustomerGroup) {
            $notLoggedInGroup = $this->groupManagement->getNotLoggedInGroup();
            $customerGroups[] = $notLoggedInGroup;
        }
        return $customerGroups;
    }

    /**
     * Retrieve option array from customer groups data
     *
     * @param array|\Magento\Customer\Api\Data\GroupInterface[] $customerGroups
     * @return array
     */
    private function getOptionArray($customerGroups)
    {
        $groupsOptions = $this->objectConverter->toOptionArray(
            $customerGroups,
            'id',
            'code'
        );
        array_unshift($groupsOptions, [
            'value' => 'all',
            'label' => __('All Groups')
        ]);
        return $groupsOptions;
    }
}
