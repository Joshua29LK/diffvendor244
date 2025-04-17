<?php

declare(strict_types=1);

namespace Amasty\ShopbyBase\Model\Integration\Shopby;

class GetSelectedFiltersSettings
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
