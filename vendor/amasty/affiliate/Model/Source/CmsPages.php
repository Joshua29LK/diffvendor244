<?php

namespace Amasty\Affiliate\Model\Source;

class CmsPages implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    protected $_blockCollection;

    /**
     * FrontLinkPosition constructor.
     * @param \Magento\Cms\Model\ResourceModel\Block\Collection $blockCollection
     */
    public function __construct(
        \Magento\Cms\Model\ResourceModel\Block\Collection $blockCollection
    ) {
        $this->_blockCollection = $blockCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $cmsBlocks = $this->_blockCollection;
        $values = [[
            'value' => '',
            'label' => '',
        ]];

        /** @var \Magento\Cms\Model\Block $block */
        foreach ($cmsBlocks as $block) {
            $values[] = [
                'value' => $block->getIdentifier(),
                'label' => $block->getTitle(),
            ];
        }

        return $values;
    }
}
