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
    protected $helperData;

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
     * @return null
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function afterExecute(CreatePost $createPost, $result)
    {
        if (!$this->helperData->isEnabled() && $createPost) {
            return null;
        }
        $customerId = null;
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->_customerSession->getCustomerId();
        }

        if ($customerId) {
            $customer = $this->helperData->getCustomerById($customerId);
            if ($this->helperData->getAutoApproveConfig()) {
                #send email notify to admin
                $this->helperData->emailNotifyAdmin($customer, $this->helperData->getEnabledNoticeAdmin());
            } else {
                #case not allow auto approve
                $actionRegister = true;
                $this->helperData->setApprovePendingById($customerId, $actionRegister);
                $this->messageManager->addNoticeMessage(__($this->helperData->getMessageAfterRegister()));
                #send email notify to admin
                $this->helperData->emailNotifyAdmin($customer, $this->helperData->getEnabledNoticeAdmin());
                #send email notify to customer
                $this->helperData->emailApprovalAction($customer, $this->helperData->getEnabledSuccessEmail(), $this->helperData->getSuccessTemplate());
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
