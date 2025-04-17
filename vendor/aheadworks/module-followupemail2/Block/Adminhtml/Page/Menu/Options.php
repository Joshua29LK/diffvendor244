<?php
namespace Aheadworks\Followupemail2\Block\Adminhtml\Page\Menu;

/**
 * Page Menu Item
 *
 * @method string getPath()
 * @method string getLabel()
 * @method string getResource()
 * @method string getController()
 * @method array getLinkAttributes()
 *
 * @method Item setLinkAttributes(array $linkAttributes)
 *
 * @package Aheadworks\Followupemail2\Block\Adminhtml\Page\Menu
 * @codeCoverageIgnore
 */
class Options extends Item
{
    /**
     * Prepare html attributes of the link
     *
     * @param string $paramValue
     * @return void
     */
    private function prepareLinkAttributes($paramValue)
    {
        $linkAttributes = \is_array($this->getLinkAttributes()) ? $this->getLinkAttributes() : [];
        if (!isset($linkAttributes['href'])) {
            $linkAttributes['href'] = $this->getUrl($this->getPath(), [$this->getParam() => $paramValue]);
        }
        $classes = [];
        if ($this->getClass()) {
            $classes[] = $this->getClass();
        }
        if ($this->isCurrent($paramValue)) {
            $classes[] = 'current';
        }
        if (count($classes) > 0) {
            $linkAttributes['class'] = implode(' ', $classes);
        }
        $this->setLinkAttributes($linkAttributes);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->isCurrent()) {
            /** @var Menu $menu */
            $menu = $this->getParentBlock();
            if ($menu) {
                $menu->setTitle($this->getLabel());
            }
        }
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function _toHtml()
    {
        if ($this->getResource() && !$this->_authorization->isAllowed($this->getResource())) {
            return '';
        }
        $html = '';
        foreach ($this->getOptions()->toOptionArray() as $option) {
            $this->setLinkAttributes([]);
            $this->prepareLinkAttributes($option['value']);
            $this->setLabel($option['label']);
            $html .= $this->fetchView($this->getTemplateFile());
        }
        return $html;
    }

    /**
     * Checks whether the item is current
     * @param int $paramValue
     *
     * @return bool
     */
    private function isCurrent($paramValue = null)
    {
        $paramName = $this->getParam();
        if ($paramValue) {
            return (
                $this->getController() == $this->getRequest()->getControllerName()
                && $paramValue == $this->getRequest()->getParam($paramName)
            );
        }
        return $this->getController() == $this->getRequest()->getControllerName();
    }
}
