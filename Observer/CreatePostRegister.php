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
 * @category    Mageplaza
 * @package     Mageplaza_CustomerApproval
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\CustomerApproval\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\App\ResponseFactory;

/**
 * Class CreatePostRegister
 * @package Mageplaza\CustomerApproval\Observer
 */
class CreatePostRegister implements ObserverInterface
{
    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var RedirectInterface
     */
    protected $_redirect;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var PhpCookieManager
     */
    private $_response;

    /**
     * CreatePostRegister constructor.
     *
     * @param HelperData        $helperData
     * @param ManagerInterface  $messageManager
     * @param RedirectFactory   $resultRedirectFactory
     * @param RedirectInterface $redirect
     * @param CustomerSession   $customerSession
     * @param ResponseFactory   $responseFactory
     */
    public function __construct(
        HelperData $helperData,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory,
        RedirectInterface $redirect,
        CustomerSession $customerSession,
        ResponseFactory $responseFactory
    )
    {
        $this->helperData            = $helperData;
        $this->messageManager        = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->_redirect             = $redirect;
        $this->_customerSession      = $customerSession;
        $this->_response             = $responseFactory;
    }

    /**
     * @param Observer $observer
     *
     * @return null|void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return null;
        }
        $customer   = $observer->getEvent()->getCustomer();
        $customerId = $customer->getId();
        $storeId     = $this->helperData->getStoreId();
        if($this->helperData->getAutoApproveConfig()){
            #case allow auto approve
            $this->helperData->approvalCustomerById($customerId);
            #send email notify to admin
            $sender = $this->helperData->getSenderAdmin();
            $sendTo      = $this->helperData->getRecipientsAdmin();
            $sendToArray = explode(',', $sendTo);
            foreach ($sendToArray as $recept) {
                $this->helperData->sendMail(
                    $recept,
                    $customer->getFirstname(),
                    $customer->getLastname(),
                    $customer->getEmail(),
                    $loginurl = NULL,
                    $this->helperData->getNoticeAdminTemplate(),
                    $storeId,
                    $sender);
            }
            #send email notify to customer
            $sendTo = $customer->getEmail();
            $sender = $this->helperData->getSenderCustomer();
            $this->helperData->sendMail(
                $sendTo,
                $customer->getFirstname(),
                $customer->getLastname(),
                $customer->getEmail(),
                $loginurl = NULL,
                $this->helperData->getSuccessTemplate(),
                $storeId,
                $sender);
        }else{
            #case not allow auto approve
            $this->helperData->setApprovePendingById($customerId);
            $this->messageManager->addNoticeMessage(__($this->helperData->getMessageAfterRegister()));
            #send email notify to admin
            $sender = $this->helperData->getSenderAdmin();
            $sendTo      = $this->helperData->getRecipientsAdmin();
            $sendToArray = explode(',', $sendTo);
            foreach ($sendToArray as $recept) {
                $this->helperData->sendMail(
                    $recept,
                    $customer->getFirstname(),
                    $customer->getLastname(),
                    $customer->getEmail(),
                    $loginurl = NULL,
                    $this->helperData->getNoticeAdminTemplate(),
                    $storeId,
                    $sender);
            }
            #send email notify to customer
            $sendTo = $customer->getEmail();
            $sender = $this->helperData->getSenderCustomer();
            $this->helperData->sendMail(
                $sendTo,
                $customer->getFirstname(),
                $customer->getLastname(),
                $customer->getEmail(),
                $loginurl = NULL,
                $this->helperData->getSuccessTemplate(),
                $storeId,
                $sender);
            #force logout customer
            $this->_customerSession->logout()->setBeforeAuthUrl($this->_redirect->getRefererUrl())
                ->setLastCustomerId($customerId);
            if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                $metadata->setPath('/');
                $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
            }
            #force redirect
            $url = $this->helperData->getUrl('customer/account/login', ['_secure' => true]);
            $this->_response->create()
                ->setRedirect($url)
                ->sendResponse();
            exit(0);

            return $this;
        }
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = ObjectManager::getInstance()->get(PhpCookieManager::class);
        }

        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(CookieMetadataFactory::class);
        }

        return $this->cookieMetadataFactory;
    }
}