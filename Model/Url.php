<?php
declare(strict_types=1);

namespace Gene\StoreSwitcher\Model;

use Laminas\Uri\UriFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

class Url
{
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager
    ) {
        $this->urlFinder = $urlFinder;
        $this->storeManager = $storeManager;
    }

    /**
     * Build URL for store using referring URL
     *
     * @param int $storeId
     * @param string $referringUrl
     * @return string
     * @throws NoSuchEntityException
     */
    public function build(
        int $storeId,
        string $referringUrl
    ): string {
        /** @var StoreInterface $store */
        $store = $this->storeManager->getStore($storeId);
        if ($store instanceof StoreInterface) {
            $storeBaseUrl = $store->getBaseUrl(); /** @phpstan-ignore-line */
            $urlParts = UriFactory::factory($referringUrl);
            $urlPath = $urlParts->getPath() ?? ''; /** @phpstan-ignore-line */
            $route = $this->urlFinder->findOneByData([
                UrlRewrite::REQUEST_PATH => ltrim($urlPath, '/'),
                UrlRewrite::STORE_ID => $storeId,
            ]);
            return $route !== null ?
                $storeBaseUrl . $route->getRequestPath() :
                $storeBaseUrl;
        }
    }
}
