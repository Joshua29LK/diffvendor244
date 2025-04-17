<?php

declare(strict_types=1);

namespace Amasty\ShopbyBase\Model\Integration\ShopbyBrand;

class GetConfigProvider
{
    /**
     * @var array
     */
    private $data;

    public function __construct(
        array $data = []
    ) {
        $this->data = $data;
    }

    /**
     * @return mixed|null
     */
    public function execute()
    {
        return $this->data['object'] ?? null;
    }
}
