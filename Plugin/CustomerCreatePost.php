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

namespace Mageplaza\CustomerApproval\Plugin;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\App\ResponseFactory;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Magento\Customer\Controller\Account\CreatePost;

/**
 * Class CustomerCreatePost
 * @package Mageplaza\CustomerApproval\Plugin
 */
class CustomerCreatePost
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
     * CustomerCreatePost constructor.
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
     * @param CreatePost $createPost
     * @param            $result
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function afterExecute(CreatePost $createPost, $result)
    {
        if ($this->helperData->isEnabled()) {
            $customerId         = $this->_customerSession->getCustomerId();
            if($customerId){
                $customer           = $this->helperData->getCustomerById($customerId);
                $storeId            = $this->helperData->getStoreId();
                $enableSendEmail    = $this->helperData->getEnabledNoticeAdmin();
                $enableEmailSuccess = $this->helperData->getEnabledSuccessEmail();
                if ($this->helperData->getAutoApproveConfig()) {
                    #case allow auto approve
                    $this->helperData->approvalCustomerById($customerId);

                    if ($enableSendEmail) {
                        #send email notify to admin
                        $sender      = $this->helperData->getSenderAdmin();
                        $sendTo      = $this->helperData->getRecipientsAdmin();
                        $sendToArray = explode(',', $sendTo);
                        foreach ($sendToArray as $recept) {
                            $this->helperData->sendMail(
                                $recept,
                                $customer->getFirstname(),
                                $customer->getLastname(),
                                $customer->getEmail(),
                                $loginurl = null,
                                $this->helperData->getNoticeAdminTemplate(),
                                $storeId,
                                $sender);
                        }
                    }
                } else {
                    #case not allow auto approve
                    $this->helperData->setApprovePendingById($customerId);
                    $this->messageManager->addNoticeMessage(__($this->helperData->getMessageAfterRegister()));
                    if ($enableSendEmail) {
                        #send email notify to admin
                        $sender      = $this->helperData->getSenderAdmin();
                        $sendTo      = $this->helperData->getRecipientsAdmin();
                        $sendToArray = explode(',', $sendTo);
                        foreach ($sendToArray as $recept) {
                            $this->helperData->sendMail(
                                $recept,
                                $customer->getFirstname(),
                                $customer->getLastname(),
                                $customer->getEmail(),
                                $loginurl = null,
                                $this->helperData->getNoticeAdminTemplate(),
                                $storeId,
                                $sender);
                        }
                    }

                    if ($enableEmailSuccess) {
                        #send email notify to customer
                        $sendTo = $customer->getEmail();
                        $sender = $this->helperData->getSenderCustomer();
                        $this->helperData->sendMail(
                            $sendTo,
                            $customer->getFirstname(),
                            $customer->getLastname(),
                            $customer->getEmail(),
                            $loginurl = null,
                            $this->helperData->getSuccessTemplate(),
                            $storeId,
                            $sender);
                    }
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
                }
            }
        }

        return $result;
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
