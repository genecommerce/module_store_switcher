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
            $this->cookieManager->setCookieForStore($store);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($newUrl);
            return $resultRedirect;
        }
        return $this->getDefaultRedirect();
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
