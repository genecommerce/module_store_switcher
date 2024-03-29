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
use Magento\Store\Api\GroupRepositoryInterface;

class Website implements ArgumentInterface
{
    /** @var StoreManagerInterface */
    private StoreManagerInterface $storeManager;

    /** @var DirectoryHelper */
    private DirectoryHelper $directoryHelper;

    /** @var Data */
    private Data $switcherHelper;

    /** @var GroupRepositoryInterface */
    private GroupRepositoryInterface $storeGroupRepository;

    /**
     * Website constructor
     *
     * @param DirectoryHelper $directoryHelper
     * @param StoreManagerInterface $storeManager
     * @param Data $switcherHelper
     * @param GroupRepositoryInterface $storeGroupRepository
     */
    public function __construct(
        DirectoryHelper $directoryHelper,
        StoreManagerInterface $storeManager,
        Data $switcherHelper,
        GroupRepositoryInterface $storeGroupRepository
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->storeManager = $storeManager;
        $this->switcherHelper = $switcherHelper;
        $this->storeGroupRepository = $storeGroupRepository;
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
