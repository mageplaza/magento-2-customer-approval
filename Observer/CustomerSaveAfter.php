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

/**
 * Class CustomerSaveAfter
 * @package Mageplaza\CustomerApproval\Observer
 */
class CustomerSaveAfter implements ObserverInterface
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
     * CustomerSaveAfter constructor.
     *
     * @param HelperData       $helperData
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        HelperData $helperData,
        ManagerInterface $messageManager
    )
    {
        $this->helperData     = $helperData;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|null|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return null;
        }
        $customer               = $observer->getEvent()->getCustomer();
        $customerId             = $customer->getId();
        $storeId                = $this->helperData->getStoreId();
        $enableSendEmailSuccess = $this->helperData->getEnabledSuccessEmail();
        if ($this->helperData->getAutoApproveConfig()) {
            #case allow auto approve
            $this->helperData->approvalCustomerById($customerId);
        } else {
            #case not allow auto approve
            $this->helperData->setApprovePendingById($customerId);
            if ($enableSendEmailSuccess) {
                #send email notify to customer
                $sendTo    = $customer->getEmail();
                $sender    = $this->helperData->getSenderCustomer();
                $loginPath = $this->helperData->getLoginUrl();
                $this->helperData->sendMail(
                    $sendTo,
                    $customer->getFirstname(),
                    $customer->getLastname(),
                    $customer->getEmail(),
                    $loginPath,
                    $this->helperData->getSuccessTemplate(),
                    $storeId,
                    $sender);
            }
        }

        return $this;
    }
}