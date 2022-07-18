<?php
declare(strict_types=1);

namespace Gene\StoreSwitcher\ViewModel;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class Website implements ArgumentInterface
{
    /** @var StoreManagerInterface */
    protected $storeManager;

    /**
     * Website constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @return array<int,array<string, mixed>>
     */
    public function getAll()
    {
        $storeList = [];
        $stores = $this->storeManager->getStores();
        /** @var StoreInterface $store */
        foreach ($stores as $store) {
            $storeId = $store->getId();
            $storeList[$storeId]['id'] = $storeId;
            $storeList[$storeId]['name'] = $store->getName();
            $storeList[$storeId]['currency'] = $store->getCurrentCurrency()->getCurrencySymbol(); /** @phpstan-ignore-line */
            $storeList[$storeId]['code'] = $store->getCode();
        }
        return $storeList;
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getCurrent()
    {
        /** @var StoreInterface $store */
        $store = $this->storeManager->getStore();
        return $store;
    }
}
