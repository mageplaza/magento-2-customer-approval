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

namespace Mageplaza\CustomerApproval\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Template;
use Mageplaza\CustomerApproval\Helper\Data;

/**
 * Class View
 * @package Mageplaza\CustomerApproval\Block\Adminhtml
 */
class View extends Template
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * View constructor.
     *
     * @param Template\Context $context
     * @param Data             $helperData
     * @param array            $data
     */
    public function __construct(
        Template\Context $context,
        Data $helperData,
        array $data = [])
    {
        parent::__construct($context, $data);

        $this->helperData = $helperData;
    }

    /**
     * @return \Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIsApproved()
    {
        $customerId = $this->getRequest()->getParam('id');
        $isApprove  = $this->helperData->getIsApproved($customerId);
        switch ($isApprove) {
            case 'approve':
                return __('Approve');
                break;
            case 'notapprove':
                return __('Not Approve');
                break;
            default:
                return __('Pending');
        }
    }
}