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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled() || !$observer->getEvent()->getOrigCustomerDataObject()) {
            return;
        }
        $customerDataObject = $observer->getEvent()->getCustomerDataObject();
        if (!$customerDataObject->getCustomAttribute('is_approved')) {
            return;
        }
        $getCustomAttribute = $customerDataObject->getCustomAttribute('is_approved');
        $previousData       = $observer->getEvent()->getOrigCustomerDataObject();
        $previousIsApproved = $previousData->getCustomAttribute('is_approved');
        $valueChangeCurrent = $this->helperData->getValueOfAttrApproved($getCustomAttribute);
        $valuePrevious      = $this->helperData->getValueOfAttrApproved($previousIsApproved);

        switch ($valueChangeCurrent) {
            case AttributeOptions::APPROVED:
                if ($valuePrevious == AttributeOptions::NOTAPPROVE || $valuePrevious == AttributeOptions::PENDING) {
                    $enableSendEmail   = $this->helperData->getEnabledApproveEmail();
                    $typeTemplateEmail = $this->helperData->getApproveTemplate();
                    $this->helperData->emailApprovalAction(
                        $customerDataObject,
                        $enableSendEmail,
                        $typeTemplateEmail
                    );
                }
                break;
            case AttributeOptions::NOTAPPROVE:
                if ($valuePrevious == AttributeOptions::APPROVED ||
                    $valuePrevious == AttributeOptions::PENDING ||
                    $valuePrevious == null
                ) {
                    $enableSendEmail   = $this->helperData->getEnabledNotApproveEmail();
                    $typeTemplateEmail = $this->helperData->getNotApproveTemplate();
                    $this->helperData->emailApprovalAction(
                        $customerDataObject,
                        $enableSendEmail,
                        $typeTemplateEmail
                    );
                }
                break;
            case AttributeOptions::PENDING:
                if ($valuePrevious == null) {
                    $enableSendEmail   = $this->helperData->getEnabledSuccessEmail();
                    $typeTemplateEmail = $this->helperData->getSuccessTemplate();
                    $this->helperData->emailApprovalAction(
                        $customerDataObject,
                        $enableSendEmail,
                        $typeTemplateEmail
                    );
                }
                break;
            default:
                break;
        }
    }
}
