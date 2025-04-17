<?php

namespace Wyomind\SimpleGoogleShopping\Plugin\Framework\Pricing\Render;

class PriceBox
{
    public $request;
    public function __construct(\Wyomind\SimpleGoogleShopping\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    /**
     * @return string
     */
    public function afterGetCacheKey($subject, $result)
    {
        $ps = $this->request->getParam('ps');
        if (isset($ps) && $ps != '') {
            return $result . '-' . $ps;
        } else {
            return $result;
        }
    }
}