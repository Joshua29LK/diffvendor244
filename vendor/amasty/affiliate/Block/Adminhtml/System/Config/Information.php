<?php

namespace Amasty\Affiliate\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Information extends Fieldset
{
    /**
     * @var string
     */
    private $userGuide = 'https://amasty.com/docs/doku.php?id=magento_2%3Aaffiliate';

    /**
     * @var array
     */
    private $incompatibleExtensions = [];

    /**
     * @var string
     */
    private $content;

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);
        $this->setContent(__('Please update Amasty Base module. Re-upload it and replace all the files.'));

        $this->_eventManager->dispatch(
            'amasty_base_add_information_content',
            ['block' => $this]
        );

        $html .= $this->getContent();
        $html .= $this->_getFooterHtml($element);

        $html = str_replace(
            'amasty_information]" type="hidden" value="0"',
            'amasty_information]" type="hidden" value="1"',
            $html
        );
        $html = preg_replace('(onclick=\"Fieldset.toggleCollapse.*?\")', '', $html);

        return $html;
    }

    /**
     * @return string
     */
    public function getUserGuide()
    {
        return $this->userGuide;
    }

    /**
     * @param string $userGuide
     */
    public function setUserGuide($userGuide)
    {
        $this->userGuide = $userGuide;
    }

    /**
     * @return array
     */
    public function getIncompatibleExtensions()
    {
        return $this->incompatibleExtensions;
    }

    /**
     * @param array incompatibleExtensions
     */
    public function setIncompatibleExtensions($incompatibleExtensions)
    {
        $this->incompatibleExtensions = $incompatibleExtensions;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}
