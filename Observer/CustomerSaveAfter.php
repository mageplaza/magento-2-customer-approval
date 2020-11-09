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
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeAction;

/**
 * Class CustomerSaveAfter
 *
 * @package Mageplaza\CustomerApproval\Observer
 */
class CustomerSaveAfter implements ObserverInterface
{
    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * CustomerSaveAfter constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $autoApproval = $this->helperData->getAutoApproveConfig();
        if (!$this->helperData->isEnabledForWebsite($customer->getWebsiteId())) {
            return;
        }

        $customerId = $customer->getId();
        $hasCustomerEdit = $this->helperData->hasCustomerEdit();
        // case create customer in adminhtml
        if (!$hasCustomerEdit && $customerId) {
            if ($autoApproval) {
                $this->helperData->approvalCustomerById($customerId, TypeAction::OTHER);
                $this->helperData->emailApprovalAction($customer, 'approve');
            } else {
                $actionRegister = false;
                $this->helperData->setApprovePendingById($customerId, $actionRegister);
                $this->helperData->emailNotifyAdmin($customer);
            }
        }
    }
}
