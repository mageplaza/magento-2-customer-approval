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

namespace Mageplaza\CustomerApproval\Plugin;

use Magento\Customer\Model\AccountManagement;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CusCollectFactory;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeNotApprove;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseFactory;

/**
 * Class CustomerAuthenticated
 * @package Mageplaza\CustomerApproval\Plugin
 */
class CustomerAuthenticated
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
     * @var ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * @var CusCollectFactory
     */
    protected $_cusCollectFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var RedirectInterface
     */
    protected $_redirect;

    /**
     * CustomerAuthenticated constructor.
     *
     * @param HelperData                      $helperData
     * @param ManagerInterface                $messageManager
     * @param ActionFlag                      $actionFlag
     * @param ResponseFactory                 $response
     * @param CusCollectFactory               $cusCollectFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param RedirectInterface               $redirect
     */
    public function __construct(
        HelperData $helperData,
        ManagerInterface $messageManager,
        ActionFlag $actionFlag,
        ResponseFactory $response,
        CusCollectFactory $cusCollectFactory,
        \Magento\Customer\Model\Session $customerSession,
        RedirectInterface $redirect
    )
    {
        $this->helperData         = $helperData;
        $this->messageManager     = $messageManager;
        $this->_actionFlag        = $actionFlag;
        $this->_response          = $response;
        $this->_cusCollectFactory = $cusCollectFactory;
        $this->_customerSession   = $customerSession;
        $this->_redirect          = $redirect;
    }

    /**
     * @param AccountManagement $subject
     * @param \Closure          $proceed
     * @param                   $username
     * @param                   $password
     *
     * @return mixed
     * @SuppressWarnings(Unused)
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundAuthenticate(
        AccountManagement $subject,
        \Closure $proceed,
        $username,
        $password
    )
    {
        if (!$this->helperData->isEnabled()) {
            return $proceed($username, $password);
        } else {
            $customerFilter = $this->_cusCollectFactory->create()->addFieldToFilter('email', $username)->getFirstItem();
            // check old customer and set approved
            $getIsapproved = null;
            if ($customerFilter->getId()) {
                $this->isOldCustomerHasCheck($customerFilter->getId());
                // check new customer logedin
                $getIsapproved = $this->helperData->getIsApproved($customerFilter->getId());
            }
            if ($customerFilter->getId() && $getIsapproved != AttributeOptions::APPROVED && $getIsapproved != null) {
                // case redirect
                $urlRedirect = $this->helperData->getUrl($this->helperData->getCmsRedirectPage(), ['_secure' => true]);
                if ($this->helperData->getTypeNotApprove() == TypeNotApprove::SHOW_ERROR || $this->helperData->getTypeNotApprove() == null) {
                    // case show error
                    $urlRedirect = $this->helperData->getUrl('customer/account/login', ['_secure' => true]);
                    $this->messageManager->addErrorMessage(__($this->helperData->getErrorMessage()));
                }

                // force logout customer
                $this->_customerSession->logout()->setBeforeAuthUrl($this->_redirect->getRefererUrl())
                    ->setLastCustomerId($customerFilter->getId());
                if ($this->helperData->getCookieManager()->getCookie('mage-cache-sessid')) {
                    $metadata = $this->helperData->getCookieMetadataFactory()->createCookieMetadata();
                    $metadata->setPath('/');
                    $this->helperData->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                }
                // force redirect
                $this->_response->create()
                    ->setRedirect($urlRedirect)
                    ->sendResponse();
            } else {
                return $proceed($username, $password);
            }
        }
    }

    /**
     * @param $customerId
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isOldCustomerHasCheck($customerId)
    {
        $getApproved = $this->helperData->getIsApproved($customerId);
        if ($getApproved == null) {
            $this->helperData->autoApprovedOldCustomerById($customerId);
        }
    }
}
