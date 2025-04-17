<?php

namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;

class FilterSettingFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Provide Filter Setting instance
     *
     * @param array $arguments
     * @return FilterSettingInterface
     * @throws \UnexpectedValueException
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(FilterSettingInterface::class, $arguments);
    }
}
