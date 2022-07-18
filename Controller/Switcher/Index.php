<?php
declare(strict_types=1);

namespace Gene\StoreSwitcher\Controller\Switcher;

use Magento\Framework\App\ActionInterface;
use Gene\StoreSwitcher\Helper\Data;
use Gene\StoreSwitcher\Model\Url;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class Index implements ActionInterface
{
    /** @var Http */
    protected $request;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var Data */
    protected $helper;

    /** @var Url */
    protected $url;

    /** @var HttpContext */
    protected $httpContext;

    /** @var StoreCookieManagerInterface */
    protected $storeCookieManager;

    /**
     * @var RedirectInterface
     */
    private RedirectInterface $redirect;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $resultRedirectFactory;

    /**
     * @param Http $request
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param HttpContext $httpContext
     * @param Url $url
     * @param RedirectInterface $redirect
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Http $request,
        StoreManagerInterface $storeManager,
        Data $helper,
        StoreCookieManagerInterface $storeCookieManager,
        HttpContext $httpContext,
        Url $url,
        RedirectInterface $redirect,
        RedirectFactory $resultRedirectFactory
    )
    {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->storeCookieManager = $storeCookieManager;
        $this->httpContext = $httpContext;
        $this->url = $url;
        $this->redirect = $redirect;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }


    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Redirect|ResultInterface|void
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $storeId = $this->request->getParam('store_id');
        $referringUrl = $this->redirect->getRefererUrl();

        if ($storeId) {
            try {
                $store = $this->storeManager->getStore($storeId);
                $currentStore = $this->storeManager->getStore();
                $newUrl = $this->url->build($store->getId(), $referringUrl);
                $this->httpContext->setValue(Store::ENTITY, $store->getCode(), $currentStore->getCode());
                $this->storeCookieManager->setStoreCookie($store);
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($newUrl);

                return $resultRedirect;
            } catch (NoSuchEntityException $e) {
                $redirectTo = $this->storeManager->getStore()->getUrl('/'); /** @phpstan-ignore-line */
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($redirectTo);
                return $resultRedirect;
            }
        }

        $redirectTo = $this->storeManager->getStore()->getUrl('/'); /** @phpstan-ignore-line */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($redirectTo);
        return $resultRedirect;
    }
}
