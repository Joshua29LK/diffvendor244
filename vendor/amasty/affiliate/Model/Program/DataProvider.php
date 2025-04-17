<?php

namespace Amasty\Affiliate\Model\Program;

use Amasty\Affiliate\Api\Data\ProgramInterface;
use Amasty\Affiliate\Model\Repository\ProgramRepository;
use Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory;
use Amasty\Affiliate\Model\Program;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    public const COMMISSION_CALCULATION_DATA_PREFIX = 'commission_calculation';

    /**
     * @var array $loadedData
     */
    private $loadedData;

    /**
     * @var DataPersistorInterface $dataPersistor
     */
    private $dataPersistor;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ProgramRepository
     */
    private $programRepository;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        UrlInterface $urlBuilder,
        ProgramRepository $programRepository,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->urlBuilder = $urlBuilder;
        $this->programRepository = $programRepository;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Program $program */
        foreach ($items as $program) {
            $id = (int)$program->getId();
            $program = $this->programRepository->get($id);
            $this->loadedData[$id] = $program->getData();
            $this->loadedData[$id]['rule_url'] = $this->urlBuilder->getUrl(
                'sales_rule/promo_quote/edit'
            );
            if ($commissionCalc = $program->getCommissionCalculation()) {
                $this->loadedData[$id][self::COMMISSION_CALCULATION_DATA_PREFIX] = $commissionCalc->getData();
                $this->loadedData[$id][self::COMMISSION_CALCULATION_DATA_PREFIX]['skus'] = implode(
                    ',',
                    $commissionCalc->getSkus()
                );
            }
        }

        $data = $this->dataPersistor->get(Program::DATA_PERSISTOR_KEY);
        if (!empty($data)) {
            $id = $data[ProgramInterface::PROGRAM_ID] ?? null;
            $this->loadedData[$id] = $data;
            $this->dataPersistor->clear(Program::DATA_PERSISTOR_KEY);
        }

        return $this->loadedData;
    }
}
