<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Queue;

use Aheadworks\Followupemail2\Api\QueueRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Queue
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Followupemail2::mail_log_actions';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var QueueCollectionFactory
     */
    private $queueCollectionFactory;

    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param QueueCollectionFactory $queueCollectionFactory
     * @param QueueRepositoryInterface $queueRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        QueueCollectionFactory $queueCollectionFactory,
        QueueRepositoryInterface $queueRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->queueRepository = $queueRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->queueCollectionFactory->create());
            $totalItems = $collection->getSize();
            foreach ($collection->getAllIds() as $queueId) {
                $this->queueRepository->deleteById($queueId);
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 email(s) have been deleted.', $totalItems)
            );
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while deleting the email(s).')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
