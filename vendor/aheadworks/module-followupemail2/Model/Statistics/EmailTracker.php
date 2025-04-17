<?php
namespace Aheadworks\Followupemail2\Model\Statistics;

use Aheadworks\Followupemail2\Model\Statistics\EmailTracker\Encryptor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Class EmailTracker
 * @package Aheadworks\Followupemail2\Model\Statistics
 */
class EmailTracker
{
    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param Encryptor $encryptor
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Encryptor $encryptor,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder
    ) {
        $this->encryptor = $encryptor;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare email content
     *
     * @param string $emailContent
     * @param array $params
     * @param int $storeId
     * @return string
     */
    public function getPreparedContent($emailContent, $params, $storeId)
    {
        libxml_use_internal_errors(true);
        /** @var \DOMDocument $dom */
        $dom = new \DOMDocument();
        if ($dom->loadHTML($emailContent)) {
            $dom = $this->addOpenTracking($dom, $params, $storeId);
            $dom = $this->addClickTracking($dom, $params, $storeId);
            $emailContent = (string)$dom->saveHTML();
        }
        libxml_use_internal_errors(false);

        return $emailContent;
    }

    /**
     * Add open tracking information
     *
     * @param \DOMDocument $dom
     * @param array $params
     * @param int $storeId
     * @return \DOMDocument
     */
    private function addOpenTracking(\DOMDocument $dom, $params, $storeId)
    {
        /** @var \DOMNodeList $elemsList */
        $elemsList = $dom->getElementsByTagName('body');
        if ($elemsList->length > 0) {
            $link = $this->urlBuilder
                ->setScope($this->storeManager->getStore($storeId))
                ->getUrl(
                    'aw_followupemail2/track/open',
                    [
                        'params' => $this->encryptor->encrypt($params),
                        '_scope_to_url' => true,
                    ]
                );
            /** @var \DOMElement $image */
            $image = $dom->createElement('img');
            $image->setAttribute('src', $link);
            $image->setAttribute('alt', '');
            $image->setAttribute('width', '1');
            $image->setAttribute('height', '1');
            $image->setAttribute('border', '0');
            if ($elemsList->item(0)) {
                $elemsList->item(0)->appendChild($image);
            }
        }

        return $dom;
    }

    /**
     * Add click tracking information
     *
     * @param \DOMDocument $dom
     * @param array $params
     * @param int $storeId
     * @return \DOMDocument
     */
    private function addClickTracking(\DOMDocument $dom, $params, $storeId)
    {
        $i = 0;
        /** @var \DOMNodeList $elemsList */
        $elemsList = $dom->getElementsByTagName('a');
        while ($i < $elemsList->length) {
            if ($elemsList->item($i)) {
                $link = $elemsList->item($i)->getAttribute('href');
            }
            if (!isset($link)) {
                continue;
            }
            if ($this->isLinkNeedToBeTracked($link)) {
                $link = $this->urlBuilder
                    ->setScope($this->storeManager->getStore($storeId))
                    ->getUrl(
                        'aw_followupemail2/track/click',
                        [
                            'url' => $this->encodeUrl($link),
                            'params' => $this->encryptor->encrypt($params),
                            '_scope_to_url' => true,
                        ]
                    );
            }
            if ($elemsList->item($i)) {
                $elemsList->item($i)->setAttribute('href', $link);
            }
            $i++;
        }

        return $dom;
    }

    /**
     * Encode URL to avoid problems with '/' and '%2F'
     *
     * @param string $originalUrl
     * @return string
     */
    private function encodeUrl($originalUrl)
    {
        return  urlencode(urlencode($originalUrl));
    }

    /**
     * Check if current link is need to be tracked
     *
     * @param string $link
     * @return bool
     */
    private function isLinkNeedToBeTracked($link)
    {
        return (strcmp("#", $link) !== 0);
    }
}
