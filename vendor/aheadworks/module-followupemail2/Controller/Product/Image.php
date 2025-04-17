<?php
namespace Aheadworks\Followupemail2\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\Repository as AssetRepository;

/**
 * Class Image
 * @package Aheadworks\Followupemail2\Controller\Product
 */
class Image extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param ImageHelper $imageHelper
     * @param AssetRepository $assetRepository
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        ImageHelper $imageHelper,
        AssetRepository $assetRepository
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $productId = $this->getRequest()->getParam('product_id');
        $width = $this->getRequest()->getParam('width');
        $height = $this->getRequest()->getParam('height');

        if ($productId) {
            try {
                $product = $this->productRepository->getById($productId);

                $this->imageHelper->init(
                    $product,
                    'product_thumbnail_image',
                    [
                        'aspect_ratio'  => true,
                        'width'         => $width,
                        'height'        => $height
                    ]
                );

                $imageUrl = $this->imageHelper->getUrl();
                return $resultRedirect->setUrl($imageUrl);
            } catch (NoSuchEntityException $e) {
            }
        }
        $imageUrl = $this->assetRepository->getUrl('spacer.gif');
        return $resultRedirect->setUrl($imageUrl);
    }
}
