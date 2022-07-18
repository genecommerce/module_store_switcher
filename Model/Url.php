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
    /** @var UrlFinderInterface */
    protected $urlFinder;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /**
     * @var UriFactory
     */
    private UriFactory $uriFactory;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param StoreManagerInterface $storeManager
     * @param UriFactory $uriFactory
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager,
        UriFactory $uriFactory
    )
    {
        $this->urlFinder = $urlFinder;
        $this->storeManager = $storeManager;
        $this->uriFactory = $uriFactory;
    }

    /**
     * @param int $storeId
     * @param string $referringUrl
     * @return string
     * @throws NoSuchEntityException
     */
    public function build(int $storeId, string $referringUrl)
    {
        /** @var StoreInterface $store */
        $store = $this->storeManager->getStore($storeId);

        if ($store instanceof StoreInterface) {
            $storeBaseUrl = $store->getBaseUrl(); /** @phpstan-ignore-line */
            $urlParts = $this->uriFactory->factory($referringUrl);
            $urlPath = $urlParts['path'] ?? ""; /** @phpstan-ignore-line */
            $route = $this->urlFinder->findOneByData(
                [
                    UrlRewrite::REQUEST_PATH => ltrim($urlPath, '/'),
                    UrlRewrite::STORE_ID => $storeId,
                ]
            );

            if ($route instanceof UrlRewrite) {
                return $storeBaseUrl . $route->getRequestPath();
            } else {
                return $storeBaseUrl;
            }
        }
    }
}
