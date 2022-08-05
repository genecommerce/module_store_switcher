<?php
declare(strict_types=1);

namespace Gene\StoreSwitcher\Plugin\Store\Model\StoreSwitcher;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\StoreSwitcher\ManageStoreCookie as Subject;

class ManageStoreCookie
{
    /**
     * @var StoreCookieManagerInterface
     */
    private $storeCookieManager;

    /**
     * @param StoreCookieManagerInterface $storeCookieManager
     */
    public function __construct(
        StoreCookieManagerInterface $storeCookieManager
    ) {
        $this->storeCookieManager = $storeCookieManager;
    }

    /**
     * @param Subject $subject
     * @param string $result
     * @param StoreInterface $fromStore
     * @param StoreInterface $targetStore
     * @param string $redirectUrl
     */
    public function afterSwitch(
        Subject $subject,
        string $result,
        StoreInterface $fromStore,
        StoreInterface $targetStore,
        string $redirectUrl
    ) {
        $targetStoreHost = parse_url($targetStore->getBaseUrl(), PHP_URL_HOST);
        $currentStoreHost = parse_url($fromStore->getBaseUrl(), PHP_URL_HOST);
        if ($currentStoreHost !== $targetStoreHost) {
            $this->storeCookieManager->deleteStoreCookie($targetStore);
        }
        return $result;
    }
}
