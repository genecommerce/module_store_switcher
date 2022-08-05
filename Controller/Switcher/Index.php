<?php
declare(strict_types=1);

namespace Gene\StoreSwitcher\Controller\Switcher;

use Gene\StoreSwitcher\Model\Url;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Controller\Store\SwitchAction\CookieManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreIsInactiveException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcherInterface;

class Index implements ActionInterface
{
    /**
     * @var Http
     */
    private Http $request;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Url
     */
    private Url $url;

    /**
     * @var RedirectInterface
     */
    private RedirectInterface $redirect;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $resultRedirectFactory;

    /**
     * @var CookieManager
     */
    private CookieManager $cookieManager;

    /**
     * @var StoreSwitcherInterface
     */
    private StoreSwitcherInterface $storeSwitcher;

    /**
     * @param Http $request
     * @param StoreManagerInterface $storeManager
     * @param Url $url
     * @param RedirectInterface $redirect
     * @param RedirectFactory $resultRedirectFactory
     * @param CookieManager $cookieManager
     * @param StoreSwitcherInterface $storeSwitcher
     */
    public function __construct(
        Http $request,
        StoreManagerInterface $storeManager,
        Url $url,
        RedirectInterface $redirect,
        RedirectFactory $resultRedirectFactory,
        CookieManager $cookieManager,
        StoreSwitcherInterface $storeSwitcher
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->redirect = $redirect;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->cookieManager = $cookieManager;
        $this->storeSwitcher = $storeSwitcher;
    }

    /**
     * @return ResponseInterface|ResultIn|void
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $storeId = $this->request->getParam('store_id');
        $referringUrl = $this->redirect->getRefererUrl();
        if ($storeId !== null) {
            try {
                $store = $this->storeManager->getStore($storeId);
                $currentStore = $this->storeManager->getStore();
            } catch (StoreIsInactiveException $e) {
                return $this->getDefaultRedirect();
            } catch (NoSuchEntityException $e) {
                return $this->getDefaultRedirect();
            }
            $newUrl = $this->url->build(
                (int) $store->getId(),
                $referringUrl
            );
            $redirectUrl = $this->storeSwitcher->switch(
                $currentStore,
                $store,
                $newUrl
            );
            $this->setStoreCookieIfApplicable(
                $store,
                $currentStore
            );
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($newUrl);
            return $resultRedirect;
        }
        return $this->getDefaultRedirect();
    }

    /**
     * We should only set the `store` cookie for target store if the host is the same
     * ie. store.com/en-gb store.com/en-us would require a store cookie store.en-gb.com store.en-us.com wouldn't
     * because the cookie domains are different we would end up setting a cookie on store.en-gb.com saying the `store` is the us one
     * and this can cause redirect loops or unexpected functionality
     *
     * @param StoreInterface $targetStore
     * @param StoreInterface $currentStore
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    private function setStoreCookieIfApplicable(
        StoreInterface $targetStore,
        StoreInterface $currentStore
    ): void {
        $targetStoreHost = parse_url($targetStore->getBaseUrl(), PHP_URL_HOST);
        $currentStoreHost = parse_url($targetStore->getBaseUrl(), PHP_URL_HOST);
        if ($currentStoreHost === $targetStoreHost) {
            $this->cookieManager->setCookieForStore($targetStore);
        }
    }

    /**
     * @return Redirect
     * @throws NoSuchEntityException
     */
    private function getDefaultRedirect(): Redirect
    {
        $redirectTo = $this->storeManager->getStore()->getUrl('/'); /** @phpstan-ignore-line */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($redirectTo);
    }
}
