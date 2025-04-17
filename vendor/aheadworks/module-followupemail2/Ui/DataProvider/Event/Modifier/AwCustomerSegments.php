<?php
namespace Aheadworks\Followupemail2\Ui\DataProvider\Event\Modifier;

use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\Component\Form\Element\MultiSelect as MultiSelectUiComponent;
use Magento\Ui\Component\Form\Element\DataType\Number as NumberUiComponentDataType;
use Magento\Framework\Stdlib\ArrayManager;
use Aheadworks\Followupemail2\Model\ThirdPartyModule\Manager as ThirdPartyModuleManager;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\AwCustomerSegment\Source\Segments as AwCustomerSegmentsSourceModel;

/**
 * Class AwCustomerSegments
 *
 * @package Aheadworks\Followupemail2\Ui\DataProvider\Event\Modifier
 */
class AwCustomerSegments implements ModifierInterface
{
    /**
     * Path to customer segments selector in the form metadata array
     */
    const PATH_TO_SELECTOR = 'customer_conditions/children/';

    /**
     * @var ThirdPartyModuleManager
     */
    private $thirdPartyModuleManager;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var AwCustomerSegmentsSourceModel
     */
    private $awCustomerSegmentsSourceModel;

    /**
     * @param ThirdPartyModuleManager $thirdPartyModuleManager
     * @param ArrayManager $arrayManager
     * @param AwCustomerSegmentsSourceModel $awCustomerSegmentsSourceModel
     */
    public function __construct(
        ThirdPartyModuleManager $thirdPartyModuleManager,
        ArrayManager $arrayManager,
        AwCustomerSegmentsSourceModel $awCustomerSegmentsSourceModel
    ) {
        $this->thirdPartyModuleManager = $thirdPartyModuleManager;
        $this->arrayManager = $arrayManager;
        $this->awCustomerSegmentsSourceModel = $awCustomerSegmentsSourceModel;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($this->thirdPartyModuleManager->isAwCustomerSegmentationModuleEnabled()) {
            $meta = $this->addAwCustomerSegmentsSelector($meta);
        }
        return $meta;
    }

    /**
     * Add to the meta array selector for customer segments
     *
     * @param array $meta
     * @return array
     */
    private function addAwCustomerSegmentsSelector($meta)
    {
        $selectorName = EventInterface::AW_CUSTOMER_SEGMENT_IDS;
        $selectorMetadata = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'dataType' => NumberUiComponentDataType::NAME,
                        'dataScope' => 'data.' . EventInterface::AW_CUSTOMER_SEGMENT_IDS,
                        'formElement' => MultiSelectUiComponent::NAME,
                        'label' => __('Customer Segments'),
                        'notice' => __('Customer Segmentation by Aheadworks is used'),
                        'options' => $this->awCustomerSegmentsSourceModel->toOptionArray(),
                        'sortOrder' => 60,
                    ],
                ],
            ],
        ];
        $meta = $this->arrayManager->set(
            self::PATH_TO_SELECTOR . $selectorName,
            $meta,
            $selectorMetadata
        );
        return $meta;
    }
}
