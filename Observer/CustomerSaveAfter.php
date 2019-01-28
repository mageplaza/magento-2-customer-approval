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
use Magento\Framework\App\RequestInterface;

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
     * @var RequestInterface
     */
    protected $_request;

    /**
     * CustomerSaveAfter constructor.
     *
     * @param HelperData       $helperData
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     */
    public function __construct(
        HelperData $helperData,
        ManagerInterface $messageManager,
        RequestInterface $request
    )
    {
        $this->helperData     = $helperData;
        $this->messageManager = $messageManager;
        $this->_request       = $request;
    }

    /**
     * @param Observer $observer
     *
     * @return null|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return null;
        }
        $customer        = $observer->getEvent()->getCustomer();
        $customerId      = $customer->getId();
        $hasCustomerEdit = $this->hasCustomerEdit();
        #case create customer in adminhtml
        if (!isset($hasCustomerEdit['customer']['is_active']) && $this->helperData->getAutoApproveConfig() && $customerId) {
            $this->helperData->approvalCustomerById($customerId);
        } else {
            #case not allow auto approve
            $actionRegister = true;
            $this->helperData->setApprovePendingById($customerId, $actionRegister);
        }
    }

    /**
     * @return array
     */
    public function hasCustomerEdit()
    {
        return $this->_request->getParams();
    }
}