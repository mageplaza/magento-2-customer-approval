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

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeAction;

/**
 * Class CustomerSaveAfter
 *
 * @package Mageplaza\CustomerApproval\Observer
 */
class CustomerLogin implements ObserverInterface
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
     * CustomerSaveAfter constructor.
     *
     * @param HelperData $helperData
     * @param Session $customerSession
     * @param RedirectInterface $redirect
     */
    public function __construct(
        HelperData $helperData,
        Session $customerSession,
        RedirectInterface $redirect
    ){
        $this->helperData = $helperData;
        $this->_customerSession = $customerSession;
        $this->_redirect = $redirect;
    }

    /**
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if (!$this->helperData->isEnabledForWebsite($customer->getWebsiteId())) {
            return;
        }

        $customerId = $customer->getId();
        $statusCustomer = $this->helperData->getIsApproved($customerId);

        if (($statusCustomer !== AttributeOptions::APPROVED && $statusCustomer !== AttributeOptions::NEW_STATUS)
        || ($statusCustomer === AttributeOptions::NEW_STATUS && !$this->helperData->getAutoApproveConfig())) {
            $this->_customerSession->logout()
            ->setBeforeAuthUrl($this->_redirect->getRefererUrl())
            ->setLastCustomerId($customerId);
            $redirectUrl = $this->helperData->getCmsRedirectPage();
            $this->_customerSession->setMpRedirectUrl($redirectUrl);
        }
    }
}
