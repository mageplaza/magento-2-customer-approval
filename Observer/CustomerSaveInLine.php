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
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;

/**
 * Class CustomerSaveInLine
 * @package Mageplaza\CustomerApproval\Observer
 */
class CustomerSaveInLine implements ObserverInterface
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
     * CustomerSaveInLine constructor.
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
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if ($this->helperData->isEnabled()) {
            $customerDataObject = $observer->getEvent()->getCustomerDataObject();
            $getCustomAttribute = $customerDataObject->getCustomAttribute('is_approved');
            $isApprovedObject   = $getCustomAttribute->__toArray();
            $attributeCode      = $isApprovedObject['attribute_code'];
            if ($attributeCode == 'is_approved') {
                #send email approve
                if ($isApprovedObject['value'] == AttributeOptions::APPROVED) {
                    $storeId = $this->helperData->getStoreId();
                    $sendTo  = $customerDataObject->getEmail();;
                    $sender   = $this->helperData->getSenderCustomer();
                    $loginurl = $this->helperData->getLoginUrl();

                    $enableSendEmail = $this->helperData->getEnabledApproveEmail();
                    if ($enableSendEmail) {
                        #send emailto customer
                        try {
                            $this->helperData->sendMail(
                                $sendTo,
                                $customerDataObject->getFirstname(),
                                $customerDataObject->getLastname(),
                                $customerDataObject->getEmail(),
                                $loginurl,
                                $this->helperData->getApproveTemplate(),
                                $storeId,
                                $sender);
                        } catch (\Exception $e) {
                            $this->messageManager->addException($e, __($e->getMessage()));
                        }
                    }
                }
                #send email not approve
                if ($isApprovedObject['value'] == AttributeOptions::NOTAPPROVE) {
                    $storeId  = $this->helperData->getStoreId();
                    $sendTo   = $customerDataObject->getEmail();
                    $sender   = $this->helperData->getSenderCustomer();
                    $loginurl = $this->helperData->getLoginUrl();

                    $enableSendEmail = $this->helperData->getEnabledNotApproveEmail();
                    if ($enableSendEmail) {
                        #send emailto customer
                        try {
                            $this->helperData->sendMail(
                                $sendTo,
                                $customerDataObject->getFirstname(),
                                $customerDataObject->getLastname(),
                                $customerDataObject->getEmail(),
                                $loginurl,
                                $this->helperData->getNotApproveTemplate(),
                                $storeId,
                                $sender);
                        } catch (\Exception $e) {
                            $this->messageManager->addException($e, __($e->getMessage()));
                        }
                    }
                }
            }
        }
    }
}