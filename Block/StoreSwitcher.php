<?php
declare(strict_types=1);

namespace Gene\StoreSwitcher\Block;

use Magento\Framework\View\Element\Template;

class StoreSwitcher extends Template
{
    /**
     * @param Template\Context $context
     * @param array<string> $data
     */
    public function __construct(
        Template\Context $context, array
        $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * test
     * @param int $storeId
     * @return string
     */
    public function getSwitchUrl(int $storeId): string
    {
        return ($storeId != null) ?
            $this->getUrl('store-switcher/switcher', ['store_id' => $storeId])
            : '';
    }
}
