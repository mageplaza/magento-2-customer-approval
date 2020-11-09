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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry as Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Mageplaza\CustomerApproval\Helper\Data;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;

/**
 * Class NotApprove
 *
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
        $this->customerAccountManagement = $customerAccountManagement;
        $this->helperData = $helperData;

        parent::__construct($context, $registry);
    }

    /**
     * @return array|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getButtonData()
    {
        $isEnableButton = $this->helperData->shouldEnableButton(AttributeOptions::NOTAPPROVE);
        if (!$isEnableButton) {
            return [];
        }

        $customerId = $this->helperData->getRequestParam('id');
        $this->helperData->setPendingCustomer($customerId);

        $data = [];
        if ($customerId) {
            $data = [
                'label' => __('Not Approved'),
                'class' => 'reset reset-password',
                'on_click' => sprintf("location.href = '%s';", $this->getApproveUrl()),
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
            ['id' => $this->getCustomerId(), 'status' => AttributeOptions::NOTAPPROVE]
        );
    }
}
