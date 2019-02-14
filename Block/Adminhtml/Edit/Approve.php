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

namespace Mageplaza\CustomerApproval\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context as Context;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\Registry as Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Mageplaza\CustomerApproval\Helper\Data;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;

/**
 * Class Approve
 *
 * @package Mageplaza\CustomerApproval\Block\Adminhtml\Edit
 */
class Approve extends GenericButton implements ButtonProviderInterface
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
     * Approve constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param AccountManagementInterface $customerAccountManagement
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AccountManagementInterface $customerAccountManagement,
        Data $helperData
    ) {
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
        $data = [];
        if (!$this->helperData->getRequestParam('id')) {
            return $data;
        }
        $customerId       = $this->getCustomerId();
        $customer = $this->helperData->getCustomerById($customerId);
        $websiteId = $customer->getWebsiteId();
        if ($this->helperData->getIsApproved($customerId) == AttributeOptions::APPROVED && $customerId) {
            return $data;
        }

        if (!$this->helperData->isEnabled() || !$this->helperData->isEnabledCAFollowWebsite($websiteId)) {
            return $data;
        }
        $cusAttributeData = $this->helperData->getIsApproved($customerId);
        if (!$cusAttributeData) {
            $actionRegister = false;
            $this->helperData->setApprovePendingById($customerId, $actionRegister);
        }

        if ($customerId) {
            $data = [
                'label'      => __('Approved'),
                'class'      => 'reset reset-password',
                'on_click'   => sprintf("location.href = '%s';", $this->getApproveUrl()),
                'sort_order' => 65,
            ];
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getApproveUrl()
    {
        return $this->getUrl(
            'mpcustomerapproval/index/approve',
            ['customer_id' => $this->getCustomerId(), 'approve_status' => AttributeOptions::APPROVED]
        );
    }
}
