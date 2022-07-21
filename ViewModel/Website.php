<?php
declare(strict_types=1);

namespace Gene\StoreSwitcher\ViewModel;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

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
     * Website constructor
     *
     * @param DirectoryHelper $directoryHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DirectoryHelper $directoryHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array<int,array<string, mixed>>
     */
    public function getAll(): array
    {
        $storeList = [];
        $stores = $this->storeManager->getStores();
        /** @var StoreInterface|Store $store */
        foreach ($stores as $store) {
            $countryCode = $this->directoryHelper->getDefaultCountry($store);
            $countryCode = $countryCode === 'GB' ?
                'UK' :
                $countryCode;
            $storeId = $store->getId();
            $storeList[$storeId]['id'] = $storeId;
            $storeList[$storeId]['name'] = $store->getName();
            $storeList[$storeId]['currency'] = $store->getCurrentCurrency()->getCurrencySymbol(); /** @phpstan-ignore-line */
            $storeList[$storeId]['code'] = $store->getCode();
            $storeList[$storeId]['country_code'] = $countryCode;
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

    public function getCurrentStoreCountry(): string
    {
        $store = $this->getCurrent();
        $country = $this->directoryHelper->getDefaultCountry($store);
        return $country === 'GB' ?
            'UK' :
            $country;
    }
}
