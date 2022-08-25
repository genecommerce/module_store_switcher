<?php
declare(strict_types=1);

namespace Gene\StoreSwitcher\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_SCHEMA_SWITCHER_ENABLED = 'storeswitcher/general/enable';
    const XML_PATH_SCHEMA_SWITCHER_EXCLUDE_STORES = 'storeswitcher/general/exclude_stores';

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getStoreSwitcherEnabled($storeId = 0)
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_SCHEMA_SWITCHER_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getExcludedStores($storeId = 0)
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_SCHEMA_SWITCHER_EXCLUDE_STORES, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
