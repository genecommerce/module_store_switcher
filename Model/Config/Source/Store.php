<?php

declare(strict_types=1);

namespace Gene\StoreSwitcher\Model\Config\Source;

use Magento\Store\Model\StoreManagerInterface;

class Store implements \Magento\Framework\Option\ArrayInterface
{
    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * Options array
     * @var array
     */
    protected $options;

    /**
     * Store constructor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Return options array
     *
     * @param boolean $isMultiselect
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $stores = $this->storeManager->getStores();
            $storeOptions = [];
            foreach ($stores as $store) {
                $storeOptions[] = [
                    'value' => $store->getId(),
                    'label' => $store->getName()
                ];
            }
            $this->options = $storeOptions;
        }
        return $this->options;
    }
}
