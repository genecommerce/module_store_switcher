<?php
declare(strict_types=1);

namespace Gene\StoreSwitcher\Block;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class StoreSwitcher extends Template implements IdentityInterface
{
    const CACHE_TAG = 'gene_storeswitcher';
    const STORESWITCHER_CACHE_LIFETIME = '3600';

    /**
     * @param StoreManagerInterface $storeManager
     * @param Template\Context $context
     * @param array<string> $data
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData(
            [
                'cache_lifetime' => self::STORESWITCHER_CACHE_LIFETIME,
                'cache_tags' => [\Magento\Store\Model\Store::CACHE_TAG, self::CACHE_TAG]
            ]
        );
    }

    /**
     * @return array|string[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIdentities()
    {
        // block_html is already added
        return [
            self::CACHE_TAG . '_' . $this->storeManager->getStore()->getId(),
            Config::TYPE_IDENTIFIER
        ];
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
