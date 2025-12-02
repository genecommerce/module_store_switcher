<?php
declare(strict_types=1);

namespace Gene\StoreSwitcher\ViewModel;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Gene\StoreSwitcher\Model\Config;
use Magento\Store\Api\GroupRepositoryInterface;

class Website implements ArgumentInterface
{
    /**
     * @param DirectoryHelper $directoryHelper
     * @param StoreManagerInterface $storeManager
     * @param Config $switcherConfig
     * @param GroupRepositoryInterface $storeGroupRepository
     */
    public function __construct(
        private readonly DirectoryHelper $directoryHelper,
        private readonly StoreManagerInterface $storeManager,
        private readonly Config $switcherConfig,
        private readonly GroupRepositoryInterface $storeGroupRepository
    ) {
    }

    /**
     * @return array<int,array<string, mixed>>
     */
    public function getAll(): array
    {
        $storeList = [];
        $stores = $this->storeManager->getStores();
        $excludedStores = $this->switcherConfig->getExcludedStores() ? $this->switcherConfig->getExcludedStores() : '';
        $excludedStores = explode(',', $excludedStores);
        /** @var StoreInterface|Store $store */
        foreach ($stores as $store) {
            $countryCode = $this->directoryHelper->getDefaultCountry($store);
            $countryCode = $countryCode === 'GB' ?
                'UK' :
                $countryCode;
            $storeId = $store->getId();
            if (!in_array($storeId, $excludedStores)) {
                $storeList[$storeId]['id'] = $storeId;
                $storeList[$storeId]['name'] = $store->getName();
                $storeList[$storeId]['display_name']
                    = $this->switcherConfig->getStoreName(ScopeInterface::SCOPE_STORE, $storeId);
                $storeList[$storeId]['currency'] = $store->getCurrentCurrency()->getCurrencySymbol();
                /** @phpstan-ignore-line */
                $storeList[$storeId]['code'] = $store->getCode();
                $storeList[$storeId]['country_code'] = $countryCode;
                $storeList[$storeId]['group_id'] = $store->getGroupId();
            }
        }
        return $storeList;
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getCurrent(): StoreInterface
    {
        return $this->storeManager->getStore();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCurrentStoreCountry(): string
    {
        $store = $this->getCurrent();
        $country = $this->directoryHelper->getDefaultCountry($store);
        return $country === 'GB' ?
            'UK' :
            $country;
    }

    /**
     * @param int|null $groupId
     * @return string
     */
    public function getStoreGroupName($groupId = null): string
    {
        $storeGroupId = ($groupId === null) ? $this->getCurrent()->getStoreGroupId() : $groupId;
        return $this->storeGroupRepository->get($storeGroupId)->getName();
    }
}
