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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;

/**
 * Class CustomerSaveInLine
 *
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
     * @param HelperData $helperData
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        HelperData $helperData,
        ManagerInterface $messageManager
    ) {
        $this->helperData     = $helperData;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     *
     * @return void|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return;
        }

        $observerGetEvent = $observer->getEvent();
        if (!$observerGetEvent->getOrigCustomerDataObject()) {
            return null;
        }
        /** @var \Magento\Framework\Api\CustomAttributesDataInterface $customerDataObject */
        $customerDataObject = $observerGetEvent->getCustomerDataObject();
        $getCustomAttribute = $customerDataObject->getCustomAttribute('is_approved');
        if (!$getCustomAttribute) {
            return;
        }
        /** @var \Magento\Framework\Api\CustomAttributesDataInterface $previousData */
        $previousData       = $observerGetEvent->getOrigCustomerDataObject();
        $previousIsApproved = $previousData->getCustomAttribute('is_approved');
        $valueChangeCurrent = $this->helperData->getValueOfAttrApproved($getCustomAttribute);
        $valuePrevious      = $this->helperData->getValueOfAttrApproved($previousIsApproved);

        switch ($valueChangeCurrent) {
            case AttributeOptions::APPROVED:
                if ($valuePrevious == AttributeOptions::NOTAPPROVE || $valuePrevious == AttributeOptions::PENDING) {
                    $this->helperData->emailApprovalAction(
                        $customerDataObject,
                        $this->helperData->getEmailSetting('approve')
                    );
                }
                break;
            case AttributeOptions::NOTAPPROVE:
                if (in_array($valuePrevious, [AttributeOptions::APPROVED, AttributeOptions::PENDING, null])
                ) {
                    $this->helperData->emailApprovalAction(
                        $customerDataObject,
                        $this->helperData->getEmailSetting('not-approve')
                    );
                }
                break;
            case AttributeOptions::PENDING:
                if ($valuePrevious == null) {
                    $this->helperData->emailApprovalAction(
                        $customerDataObject,
                        $this->helperData->getEmailSetting('success')
                    );
                }
                break;
            default:
                break;
        }
    }
}
