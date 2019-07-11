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

namespace Mageplaza\CustomerApproval\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\CustomerApproval\Helper\Data;

/**
 * Class View
 *
 * @package Mageplaza\CustomerApproval\Block\Adminhtml\Edit\Tab
 */
class View extends Template
{
    /**
     * @var Data
     */
    public $helperData;

    /**
     * View constructor.
     *
     * @param Template\Context $context
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @return mixed|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getApprovedLabel()
    {
        $customerId = $this->getRequest()->getParam('id');
        $value = $this->helperData->getIsApproved($customerId);

        return $this->helperData->getApprovalLabel($value);
    }

    /**
     * @return int|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isEnabled()
    {
        $customerId = $this->getRequest()->getParam('id');
        $customer = $this->helperData->getCustomerById($customerId);

        return $this->helperData->isEnabledForWebsite($customer->getWebsiteId());
    }
}
