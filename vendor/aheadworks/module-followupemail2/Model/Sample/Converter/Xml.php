<?php
namespace Aheadworks\Followupemail2\Model\Sample\Converter;

/**
 * Class Xml
 * @package Aheadworks\Followupemail2\Model\Sample\Converter
 * @codeCoverageIgnore
 */
class Xml implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $output = [];
        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        $tagNames = [
            'campaign' => [
                'event' => [
                    'email' => []
                ]
            ]
        ];

        $output = $this->convertElementsByTagNames($source, $tagNames);

        return $output;
    }

    /**
     * Convert elements by tag name
     *
     * @param \DOMDocument $source
     * @param array $tags
     * @param string|null $tag
     * @return array
     */
    private function convertElementsByTagNames($source, $tags, $tag = null)
    {
        $output = [];
        foreach ($tags as $tagName => $tagChildren) {
            if ($tag && $tag != $tagName) {
                continue;
            }
            $elements = $source->getElementsByTagName($tagName);
            foreach ($elements as $element) {
                $elementData = [];
                /** @var \DOMElement $element */
                foreach ($element->childNodes as $child) {
                    if (!$child instanceof \DOMElement) {
                        continue;
                    }
                    $elementData[$child->nodeName] = $child->nodeValue;
                }

                if (is_array($tagChildren)) {
                    foreach ($tagChildren as $tagChildName => $tagContent) {
                        $elementData[$tagChildName] = $this->convertElementsByTagNames(
                            $element,
                            $tagChildren,
                            $tagChildName
                        );
                    }
                    $output[] = $elementData;
                } else {
                    $output = $elementData;
                }
            }
        }
        return $output;
    }
}
