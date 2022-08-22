<?php
declare(strict_types=1);

namespace Gene\StoreSwitcher\ViewModel;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Gene\StoreSwitcher\Helper\Data;

class Website implements ArgumentInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DirectoryHelper
     */
    private $directoryHelper;

    /**
     * @var Data
     */
    private $switcherHelper;

    /**
     * Website constructor
     *
     * @param DirectoryHelper $directoryHelper
     * @param StoreManagerInterface $storeManager
     * @param Data $switcherHelper
     */
    public function __construct(
        DirectoryHelper $directoryHelper,
        StoreManagerInterface $storeManager,
        Data $switcherHelper
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->storeManager = $storeManager;
        $this->switcherHelper = $switcherHelper;
    }

    /**
     * @return array<int,array<string, mixed>>
     */
    public function getAll(): array
    {
        $storeList = [];
        $stores = $this->storeManager->getStores();
        $excludedStores = $this->switcherHelper->getExcludedStores() ? $this->switcherHelper->getExcludedStores() : '';
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
                $storeList[$storeId]['currency'] = $store->getCurrentCurrency()->getCurrencySymbol();
                /** @phpstan-ignore-line */
                $storeList[$storeId]['code'] = $store->getCode();
                $storeList[$storeId]['country_code'] = $countryCode;
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
}
