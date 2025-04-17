<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Banner;

class Save extends \Amasty\Affiliate\Controller\Adminhtml\Banner
{
    private $validator;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Amasty\Affiliate\Api\BannerRepositoryInterface $bannerRepository,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Amasty\Affiliate\Model\BannerFactory $bannerFactory,
        \Amasty\Affiliate\Model\ResourceModel\Banner\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Url\Validator $validator
    ) {
        $this->validator = $validator;
        parent::__construct(
            $context,
            $resultPageFactory,
            $bannerRepository,
            $filter,
            $bannerFactory,
            $collectionFactory,
            $coreRegistry
        );
    }

    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                /** @var \Amasty\Affiliate\Model\Banner $model */
                $model = $this->bannerFactory->create();
                $data = $this->getRequest()->getPostValue();
                if (!$this->validator->isValid($data['link'])) {
                    $this->messageManager->addErrorMessage(__('Please use valid url'));
                    $id = (int)$this->getRequest()->getParam('id');
                    if (!empty($id)) {
                        $this->_redirect('amasty_affiliate/banner/edit', ['id' => $id]);
                    } else {
                        $this->_redirect('amasty_affiliate/banner/new');
                    }
                    return;
                }
                $data = $this->imagePreprocessing($data);
                $model->addData($this->_filterPostData($data));
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model = $this->bannerRepository->get($id);
                }

                $this->bannerRepository->save($model);
                $this->messageManager->addSuccessMessage(__('The banner is saved.'));

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amasty_affiliate/banner/edit', ['id' => $model->getBannerId()]);
                    return;
                }
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('amasty_affiliate/banner/edit', ['id' => $id]);
                } else {
                    $this->_redirect('amasty_affiliate/banner/new');
                }
                return;
            }

        }
        $this->_redirect('amasty_affiliate/banner/index');
    }

    public function imagePreprocessing($data)
    {
        if (empty($data['image'])) {
            unset($data['image']);
            $data['image']['delete'] = true;
        }
        return $data;
    }

    protected function _filterPostData(array $rawData)
    {
        $data = $rawData;
        //It is a workaround to prevent saving this data in banner(like in category) model and it has to be refactored in future
        if (isset($data['image']) && is_array($data['image'])) {
            if (!empty($data['image']['delete'])) {
                $data['image'] = null;
            } else {
                if (isset($data['image'][0]['name']) && isset($data['image'][0]['tmp_name'])) {
                    $data['image'] = $data['image'][0]['name'];
                } else {
                    unset($data['image']);
                }
            }
        }
        return $data;
    }
}
