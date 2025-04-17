<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutSuccessPage
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CheckoutSuccessPage\Helper;

class ImageHelper
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $imageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $catalogHelperImg;

    /**
     * ImageHelper constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Image $catalogHelperImg
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Image $catalogHelperImg
    ) {
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
        $this->catalogHelperImg = $catalogHelperImg;
    }

    /**
     * @param $product
     * @return string
     */
    public function getImageThumb($product)
    {
        return $this->catalogHelperImg->init($product, 'product_page_image_small')
        ->setImageFile($product->getImage())
        ->getUrl();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @param $image
     * @param null $width
     * @param null $height
     * @return string
     * @throws \Exception
     */
    public function resize($image, $width = null, $height = null)
    {
        $absolutePath = $this->filesystem
        ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
        ->getAbsolutePath('image/').$image;

        $destination = $this->filesystem
        ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
        ->getAbsolutePath('resized/'.$width.'/').$image;
        $imageResize = $this->imageFactory->create();
        $imageResize->open($absolutePath);
        $imageResize->constrainOnly(true);
        $imageResize->keepTransparency(true);
        $imageResize->keepFrame(false);
        $imageResize->keepAspectRatio(true);
        $imageResize->resize($width, $height);
        $imageResize->save($destination);

        $resizedURL = $this->getMediaBaseUrl().'resized/'.$width.'/'.$image;
        return $resizedURL;
    }
}
