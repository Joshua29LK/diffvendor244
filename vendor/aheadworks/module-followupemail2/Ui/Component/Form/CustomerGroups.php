<?php
namespace Aheadworks\Followupemail2\Ui\Component\Form;

use Magento\Ui\Component\Form\Element\MultiSelect;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Aheadworks\Followupemail2\Model\Source\CustomerGroups as CustomerGroupsSource;

/**
 * Class CustomerGroups
 * @package Aheadworks\Followupemail2\Ui\Component\Form
 * @codeCoverageIgnore
 */
class CustomerGroups extends MultiSelect
{
    /**
     * @param ContextInterface $context
     * @param array|OptionSourceInterface|null $options
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        $options = null,
        array $components = [],
        array $data = []
    ) {
        if (empty($options)) {
            $options = $data['options'];
        }
        parent::__construct($context, $options, $components, $data);
        $this->prepareOptionsObject();
    }

    /**
     * Prepare options object for options array retrieving
     *
     * @return CustomerGroupsSource|array|OptionSourceInterface|null
     */
    private function prepareOptionsObject()
    {
        if (!empty($this->options)) {
            if ($this->options instanceof CustomerGroupsSource) {
                $dataProvider = $this->context->getDataProvider();
                if ($dataProvider->isAllowedForGuests()) {
                    $this->options->setIsNeedToAddNotLoggedInCustomerGroup(true);
                }
            }
        }
        return $this->options;
    }
}
