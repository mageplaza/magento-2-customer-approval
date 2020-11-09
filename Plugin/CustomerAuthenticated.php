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

use Closure;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CusCollectFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeNotApprove;

/**
 * Class CustomerAuthenticated
 *
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
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * @var CusCollectFactory
     */
    protected $_cusCollectFactory;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var RedirectInterface
     */
    protected $_redirect;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CustomerAuthenticated constructor.
     *
     * @param HelperData $helperData
     * @param ManagerInterface $messageManager
     * @param ResponseFactory $response
     * @param CusCollectFactory $cusCollectFactory
     * @param Session $customerSession
     * @param RedirectInterface $redirect
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        HelperData $helperData,
        ManagerInterface $messageManager,
        ResponseFactory $response,
        CusCollectFactory $cusCollectFactory,
        Session $customerSession,
        RedirectInterface $redirect,
        StoreManagerInterface $storeManager
    ) {
        $this->helperData = $helperData;
        $this->messageManager = $messageManager;
        $this->_response = $response;
        $this->_cusCollectFactory = $cusCollectFactory;
        $this->_customerSession = $customerSession;
        $this->_redirect = $redirect;
        $this->storeManager = $storeManager;
    }

    /**
     * @param AccountManagement $subject
     * @param Closure $proceed
     * @param $username
     * @param $password
     *
     * @return                   mixed
     * @throws                   InputException
     * @throws                   LocalizedException
     * @throws                   NoSuchEntityException
     * @throws                   FailureToSendException
     * @SuppressWarnings(Unused)
     */
    public function aroundAuthenticate(
        AccountManagement $subject,
        Closure $proceed,
        $username,
        $password
    ) {
        $result = $proceed($username, $password);

        if (!$this->helperData->isEnabled()) {
            return $result;
        }

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerFilter = $this->_cusCollectFactory->create()
            ->addFieldToFilter('email', $username)
            ->addFieldToFilter('website_id', $websiteId)
            ->getFirstItem();

        // check old customer and set approved
        $getIsApproved = null;

        if ($customerId = $customerFilter->getId()) {
            $this->isOldCustomerHasCheck($customerId);
            // check new customer logedin
            $getIsApproved = $this->helperData->getIsApproved($customerId);
        }

        if ($customerId && $getIsApproved !== AttributeOptions::APPROVED && !empty($getIsApproved)) {
            // case redirect
            $urlRedirect = $this->helperData->getUrl($this->helperData->getCmsRedirectPage(), ['_secure' => true]);
            if ($this->helperData->getTypeNotApprove() === TypeNotApprove::SHOW_ERROR
                || empty($this->helperData->getTypeNotApprove())) {
                // case show error
                $urlRedirect = $this->helperData->getUrl('customer/account/login', ['_secure' => true]);
                $this->messageManager->addErrorMessage(__($this->helperData->getErrorMessage()));
            }

            // force logout customer
            $this->_customerSession->logout()
                ->setBeforeAuthUrl($this->_redirect->getRefererUrl())
                ->setLastCustomerId($customerId);

            // processCookieLogout
            $this->helperData->processCookieLogout();

            // force redirect
            return $this->_response->create()->setRedirect($urlRedirect)->sendResponse();
        }

        return $result;
    }

    /**
     * @param $customerId
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function isOldCustomerHasCheck($customerId)
    {
        $getApproved = $this->helperData->getIsApproved($customerId);

        if (empty($getApproved)) {
            $this->helperData->autoApprovedOldCustomerById($customerId);
        }
    }
}
