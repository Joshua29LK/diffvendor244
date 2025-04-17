<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Program;

use Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterfaceFactory;
use Amasty\Affiliate\Api\Data\ProgramInterface;
use Amasty\Affiliate\Api\ProgramRepositoryInterface;
use Amasty\Affiliate\Controller\Adminhtml\Program;
use Amasty\Affiliate\Model\Program as ProgramModel;
use Amasty\Affiliate\Model\ProgramFactory;
use Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\ResourceModel\CustomerRepository;

class Save extends Program
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var ProgramCommissionCalculationInterfaceFactory
     */
    private $commissionCalculationInterfaceFactory;

    public function __construct(
        Action\Context $context,
        ProgramRepositoryInterface $programRepository,
        PageFactory $resultPageFactory,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ProgramFactory $programFactory,
        Date $dateFilter,
        DataPersistorInterface $dataPersistor,
        Registry $coreRegistry,
        CustomerRepository $customerRepository,
        ProgramCommissionCalculationInterfaceFactory $commissionCalculationInterfaceFactory
    ) {
        parent::__construct(
            $context,
            $programRepository,
            $resultPageFactory,
            $filter,
            $collectionFactory,
            $programFactory,
            $dateFilter,
            $dataPersistor,
            $coreRegistry
        );
        $this->customerRepository = $customerRepository;
        $this->commissionCalculationInterfaceFactory = $commissionCalculationInterfaceFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            try {
                $id = (int)$this->getRequest()->getParam('program_id');
                if ($id) {
                    $model = $this->programRepository->get($id);
                } else {
                    $model = $this->programFactory->create();
                }
                if (!empty($data['available_customers'])) {
                    $customers = explode(',', $data['available_customers']);
                    foreach ($customers as $customer) {
                        $this->customerRepository->getById($customer);
                    }
                }
                if (isset($data['available_groups']) && is_array($data['available_groups'])) {
                    $data['available_groups'] = implode(',', $data['available_groups']);
                }
                if (isset($data['available_customers'])) {
                    $data['available_customers'] = str_replace(' ', '', $data['available_customers']);
                }
                $model->loadPost($data);
                $this->processCommissionCalculation($model, $data);
                $this->programRepository->save($model);
                $this->messageManager->addSuccessMessage(__('The program is saved.'));
                $this->dataPersistor->clear(ProgramModel::DATA_PERSISTOR_KEY);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amasty_affiliate/program/edit', ['id' => $model->getProgramId()]);
                    return;
                }
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                $this->dataPersistor->set(ProgramModel::DATA_PERSISTOR_KEY, $data);
                $id = (int)$this->getRequest()->getParam('program_id');
                if (!empty($id)) {
                    $this->_redirect('amasty_affiliate/program/edit', ['id' => $id]);
                } else {
                    $this->_redirect('amasty_affiliate/program/new');
                }
                return;
            }

        }
        $this->_redirect('amasty_affiliate/program/index');
    }

    private function processCommissionCalculation(ProgramInterface $model, array $data)
    {
        if (isset($data['commission_calculation'])) {
            $commissionCalc = $this->commissionCalculationInterfaceFactory->create();
            $commissionCalc->setData($data['commission_calculation']);
            $commissionCalc->setSkus(array_filter(
                array_map('trim', explode(',', $data['commission_calculation']['skus']))
            ));
            $model->setCommissionCalculation($commissionCalc);
        }
    }
}
