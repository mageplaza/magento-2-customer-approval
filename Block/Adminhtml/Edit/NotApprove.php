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

namespace Mageplaza\CustomerApproval\Block\Adminhtml\Edit;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Mageplaza\CustomerApproval\Helper\Data;

/**
 * Class NotApprove
 * @package Mageplaza\CustomerApproval\Block\Adminhtml\Edit
 */
class NotApprove extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * NotApprove constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param AccountManagementInterface            $customerAccountManagement
     * @param Data                                  $helperData
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        AccountManagementInterface $customerAccountManagement,
        Data $helperData
    )
    {
        parent::__construct($context, $registry);
        $this->customerAccountManagement = $customerAccountManagement;
        $this->helperData                = $helperData;
    }

    /**
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getButtonData()
    {
        if (!$this->helperData->isEnabled()) {
            return null;
        }

        $customerId            = $this->getCustomerId();
        $customerAttributeData = $this->helperData->getIsApproved($customerId);
        if (!$customerAttributeData || $customerAttributeData == NULL) {
            $this->helperData->setApprovePendingById($customerId);
        }
        $data = [];
        if ($customerId) {
            $data = [
                'label'      => __('Not Approve'),
                'class'      => 'reset reset-password',
                'on_click'   => sprintf("location.href = '%s';", $this->getApproveUrl()),
                'sort_order' => 65,
            ];
        }
        if ($this->helperData->getIsApproved($customerId) == 'notapprove' && $customerId) {
            return NULL;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getApproveUrl()
    {
        return $this->getUrl('mpcustomerapproval/index/approve', ['customer_id' => $this->getCustomerId(), 'approve_status' => 'notapprove']);
    }
}
