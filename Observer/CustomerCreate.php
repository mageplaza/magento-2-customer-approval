<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_CustomerApproval
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\CustomerApproval\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Magento\Framework\Message\ManagerInterface;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeNotApprove;

/**
 * Class CustomerSaveAfter
 *
 * @package Mageplaza\CustomerApproval\Observer
 */
class CustomerCreate implements ObserverInterface
{
    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var Session
     */
    private $_customerSession;

    /**
     * @var RedirectInterface
     */
    private $_redirect;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CustomerCreate constructor.
     * @param HelperData $helperData
     * @param Session $customerSession
     * @param RedirectInterface $redirect
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        HelperData $helperData,
        Session $customerSession,
        RedirectInterface $redirect,
        ManagerInterface $messageManager,
        RequestInterface $request,
        StoreManagerInterface $storeManager
    ){
        $this->helperData = $helperData;
        $this->_customerSession = $customerSession;
        $this->_redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        if (!$this->helperData->isEnabledForWebsite($customer->getWebsiteId())) {
            return $this;
        }

        $customerId = $customer->getId();
        $statusCustomer = $this->helperData->getIsApproved($customerId);

        if ($statusCustomer === AttributeOptions::NEW_STATUS
            && !$this->helperData->getAutoApproveConfig($customer->getStoreId())
            && $this->request->isAjax()
        ) {
            $this->_customerSession->logout()
            ->setBeforeAuthUrl($this->_redirect->getRefererUrl())
            ->setLastCustomerId($customerId);

            $urlRedirect = $this->helperData->getUrl($this->helperData->getCmsRedirectPage(), ['_secure' => true]);

            if (($this->helperData->getTypeNotApprove() === TypeNotApprove::SHOW_ERROR
                    || empty($this->helperData->getTypeNotApprove()))
                && $this->request->isAjax()) {
                // case show error
                throw new LocalizedException(new \Magento\Framework\Phrase(
                    __(
                        "Thank you for registering with %1. However, your account requires approval before you can log in.",
                        $this->storeManager->getStore()->getFrontendName()
                    )
                ));
            }

            $this->_customerSession->setMpRedirectUrl($urlRedirect);

            throw new LocalizedException(new \Magento\Framework\Phrase(
                __(
                    "Thank you for registering with %1. However, your account requires approval before you can log in.",
                    $this->storeManager->getStore()->getFrontendName()
                )
            ));
        }
    }
}
