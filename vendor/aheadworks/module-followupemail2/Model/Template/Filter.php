<?php
namespace Aheadworks\Followupemail2\Model\Template;

use Aheadworks\Followupemail2\Model\Template\Resolver\Product;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class Filter
 *
 * @package Aheadworks\Followupemail2\Model\Template
 * @codeCoverageIgnore
 */
class Filter extends \Magento\Newsletter\Model\Template\Filter
{
    /**
     * For pattern
     */
    const CONSTRUCTION_FOR_PATTERN = '/{{for\s*(.*?)\s*in\s*(.*?)}}(.*?){{\\/for\s*}}/si';

    /**
     * Thumbnail directive parameter names
     */
    const THUMBNAIL_SOURCE              = 'source';
    const THUMBNAIL_WIDTH               = 'width';
    const THUMBNAIL_HEIGHT              = 'height';

    /**#@+
     * Names of parameters for productUrl directive
     */
    const PRODUCT_URL_SOURCE            = 'source';
    /**#@-*/

    /**
     * Filter the string as template
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        $this->_modifiers['formatPrice'] = [$this, 'modifierFormatPrice'];
        $this->_modifiers['formatDecimal'] = [$this, 'modifierFormatDecimal'];
        try {
            $value = $this->process($value);
        } catch (\Exception $e) {
            // Since a single instance of this class can be used to filter content multiple times, reset callbacks to
            // prevent callbacks running for unrelated content (e.g., email subject and email body)
            $this->resetAfterFilterCallbacks();

            if ($this->_appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
                $value = sprintf(__('Error filtering template: %s'), $e->getMessage());
            } else {
                $value = __("We're sorry, an error has occurred while generating this email.")->getText();
            }
            $this->_logger->critical($e);
        }
        return $value;
    }

    /**
     * Process string
     *
     * @param string $value
     * @return string
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function process($value)
    {
        // "depend", "if", "template" and "for" directives should be first
        $directives = [
            self::CONSTRUCTION_DEPEND_PATTERN => 'dependDirective',
            self::CONSTRUCTION_IF_PATTERN => 'ifDirective',
            self::CONSTRUCTION_TEMPLATE_PATTERN => 'templateDirective',
            self::CONSTRUCTION_FOR_PATTERN => 'forDirective',
        ];
        foreach ($directives as $pattern => $directive) {
            if (preg_match_all($pattern, $value ?? '', $constructions, PREG_SET_ORDER)) {
                foreach ($constructions as $construction) {
                    $callback = [$this, $directive];
                    if (!is_callable($callback)) {
                        continue;
                    }
                    try {
                        $replacedValue = call_user_func($callback, $construction);
                    } catch (\Exception $e) {
                        throw $e;
                    }
                    $value = str_replace($construction[0], $replacedValue, $value);
                }
            }
        }

        if (preg_match_all(self::CONSTRUCTION_PATTERN, $value ?? '', $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                $callback = [$this, $construction[1] . 'Directive'];
                if (!is_callable($callback)) {
                    continue;
                }
                try {
                    $replacedValue = call_user_func($callback, $construction);
                } catch (\Exception $e) {
                    throw $e;
                }
                $value = str_replace($construction[0], $replacedValue, $value);
            }
        }

        $value = $this->afterFilter($value);
        return $value;
    }

    /**
     * forDirective
     *
     * @param string[] $construction
     * @return string
     * @throws \Exception
     */
    public function forDirective($construction)
    {
        $content = '';
        if (count($this->templateVars) == 0) {
            $content = $construction[0];
        }
        $iterated = $this->getVariable($construction[2], []);
        if (is_array($iterated) || $iterated instanceof \IteratorAggregate) {
            foreach ($iterated as $variable) {
                $this->templateVars[$construction[1]] = $variable;
                $content .= $this->process($construction[3]);
            }
        }

        return $this->sanitizeValue($content);
    }

    /**
     * widgetDirective
     *
     * @param \string[] $construction
     * @return string
     */
    public function widgetDirective($construction)
    {
        $params = $this->getParameters($construction[2]);
        $params['area'] = 'frontend';

        // Determine what name block should have in layout
        $name = null;
        if (isset($params['name'])) {
            $name = $params['name'];
        }
        // validate required parameter type or id
        if (!empty($params['type'])) {
            $type = $params['type'];
        } elseif (!empty($params['id'])) {
            $preConfigured = $this->_widgetResource->loadPreconfiguredWidget($params['id']);
            $type = $preConfigured['widget_type'] ?? '';
            $params = $preConfigured['parameters'] ?? [];
        } else {
            return '';
        }
        // we have no other way to avoid fatal errors for type like 'cms/widget__link', '_cms/widget_link' etc.
        $xml = $this->_widget->getWidgetByClassType($type);
        if ($xml === null) {
            return '';
        }
        // define widget block and check the type is instance of Widget Interface
        $widget = $this->_layout->createBlock($type, $name, ['data' => $params]);
        if (!$widget instanceof \Magento\Widget\Block\BlockInterface) {
            return '';
        }
        $result = $widget->toHtml();

        return preg_replace("/{{/", "&#123;&#123;", $result);
    }

    /**
     * modifierFormatPrice
     *
     * @param string $value
     * @return mixed
     */
    public function modifierFormatPrice($value)
    {
        if (isset($this->templateVars['store'])) {
            $value = $this->templateVars['store']->getCurrentCurrency()->format($value);
        }
        return $value;
    }

    /**
     * modifierFormatDecimal
     *
     * @param string $value
     * @return string
     */
    public function modifierFormatDecimal($value)
    {
        if (is_numeric($value)) {
            $params = func_get_args();
            array_shift($params);
            if (!count($params)) {
                $value = number_format($value);
            } elseif (count($params) == 1) {
                $value = number_format($value, $params[0]);
            } elseif (count($params) == 3) {
                $value = number_format($value, $params[0], $params[1], $params[2]);
            }
        }
        return $value;
    }

    /**
     * thumbnailDirective
     * Returns link to product thumbnail
     * Usage: {{thumbnail source="" width="" height=""}}
     *
     * @param string[] $construction
     * @return string
     * @throws \Exception
     */
    public function thumbnailDirective($construction)
    {
        $params = $this->getParameters($construction[2]);

        if (isset($params[self::THUMBNAIL_SOURCE]) && $params[self::THUMBNAIL_SOURCE]) {
            $source = $params[self::THUMBNAIL_SOURCE];
            if (is_object($source)) {
                if ($source instanceof ProductInterface) {
                    /** @var ProductInterface $product */
                    $product = $source;
                    $productId = $product->getId();
                } else {
                    throw new \Exception(__('The object specified is not a product to take a thumbnail from'));
                }
            } elseif (is_scalar($source)) {
                $productId = $source;
            } else {
                throw new \Exception(__('Wrong object type'));
            }
        } else {
            throw new \Exception(__('No source parameter is specified; there is nowhere to take the thumbnail from'));
        }

        $imgWidth = isset($params[self::THUMBNAIL_WIDTH]) ?
            $params[self::THUMBNAIL_WIDTH] :
            null;
        $imgHeight = isset($params[self::THUMBNAIL_HEIGHT]) ?
            $params[self::THUMBNAIL_HEIGHT] :
            null;

        if (isset($product)) {
            $this->urlModel->setScope($this->_storeManager->getStore($product->getStoreId()));
        }
        $url = $this->urlModel
            ->getUrl(
                'aw_followupemail2/product/image',
                [
                    'product_id' => $productId,
                    'width'  => $imgWidth,
                    'height' => $imgHeight,
                    '_scope_to_url' => true,
                ]
            );

        return $this->sanitizeValue($url);
    }

    /**
     * productUrlDirective
     * Returns link to product
     * Usage:
     * {{productUrl source="$productId"}}
     * {{productUrl source="$product"}}
     * {{productUrl source="$quoteItem"}}
     * {{productUrl source="$orderItem"}}
     *
     * @param string[] $construction
     * @return string
     * @throws \Exception
     */
    public function productUrlDirective($construction)
    {
        $parameters = $this->getParameters($construction[2]);
        $productId = null;
        $storeId = null;

        if (isset($parameters[self::PRODUCT_URL_SOURCE])
            && $parameters[self::PRODUCT_URL_SOURCE]
        ) {
            $source = $parameters[self::PRODUCT_URL_SOURCE];
            if (is_object($source)) {
                $productId = $this->getProductResolver()->getProductId($source);
                $storeId = $source->getStoreId();
            } elseif (is_scalar($source)) {
                $productId = $source;
            } else {
                throw new \Exception(__('Wrong object type to take a product URL from'));
            }
        } else {
            throw new \Exception(__('No source parameter is specified; there is nowhere to take a product URL from'));
        }

        if (empty($productId)) {
            throw new \Exception(__('Incorrect source to take a product URL from'));
        }

        if (isset($storeId)) {
            $this->urlModel->setScope($this->_storeManager->getStore($storeId));
        }

        $url = $this->urlModel
            ->getUrl(
                'catalog/product/view',
                [
                    'id' => $productId,
                    '_scope_to_url' => true,
                ]
            );

        return $this->sanitizeValue($url);
    }

    /**
     * {@inheritdoc}
     */
    public function isStrictMode(): bool
    {
        return false;
    }

    /**
     * Get product resolver
     *
     * @return Product
     */
    private function getProductResolver()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(Product::class);
    }

    /**
     * Sanitize directive result value
     *
     * @param bool|string $value
     * @return bool|string
     */
    private function sanitizeValue($value)
    {
        return is_bool($value) ? $value : str_replace(['{', '}'], '', (string) $value);
    }
}
