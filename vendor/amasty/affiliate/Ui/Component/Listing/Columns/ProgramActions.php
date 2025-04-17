<?php

namespace Amasty\Affiliate\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class ProgramActions extends Column
{
    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                $item[$name]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_affiliate/program/edit',
                        ['id' => $item['program_id']]
                    ),
                    'label' => __('Edit')
                ];
                $item[$name]['enable'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_affiliate/program/changeStatus',
                        ['id' => $item['program_id'], 'status' => 1]
                    ),
                    'label' => __('Enable')
                ];
                $item[$name]['disable'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_affiliate/program/changeStatus',
                        ['id' => $item['program_id'], 'status' => 0]
                    ),
                    'label' => __('Disable')
                ];
                $item[$name]['delete'] = [
                    'href' => $this->urlBuilder->getUrl('amasty_affiliate/program/delete', ['id' => $item['program_id']]),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete ${ $.$data.name }'),
                        'message' => __('Are you sure you wan\'t to delete a ${ $.$data.name } record?')
                    ]
                ];
            }
        }

        return $dataSource;
    }
}
