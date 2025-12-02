<?php

declare(strict_types=1);

namespace Gene\StoreSwitcher\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_SCHEMA_SWITCHER_ENABLED = 'storeswitcher/general/enable';
    public const XML_PATH_SCHEMA_SWITCHER_EXCLUDE_STORES = 'storeswitcher/general/exclude_stores';
    public const XML_PATH_SCHEMA_SWITCHER_STORE_NAME = 'storeswitcher/general/store_name';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @param string $scopeType
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function isEnabled(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        int|string|null $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SCHEMA_SWITCHER_ENABLED, $scopeType, $scopeCode);
    }

    /**
     * @param string $scopeType
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getExcludedStores(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        int|string|null $scopeCode = null
    ): string {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_SCHEMA_SWITCHER_EXCLUDE_STORES, $scopeType, $scopeCode) ?? '';
    }

    /**
     * @param string $scopeType
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getStoreName(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        int|string|null $scopeCode = null
    ): string {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_SCHEMA_SWITCHER_STORE_NAME, $scopeType, $scopeCode) ?? '';
    }
}
