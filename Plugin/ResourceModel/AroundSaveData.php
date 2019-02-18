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

namespace Mageplaza\CustomerApproval\Plugin\ResourceModel;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;

/**
 * Class AroundSaveData
 *
 * @package Mageplaza\CustomerApproval\Plugin\ResourceModel
 */
class AroundSaveData
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * AroundSaveData constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param CustomerRepository $subject
     * @param \Closure $proceed
     * @param CustomerInterface $customer
     * @param null $passwordHash
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundSave(
        CustomerRepository $subject,
        \Closure $proceed,
        CustomerInterface $customer,
        $passwordHash = null
    ) {
        if (!$this->helperData->isEnabled() || !$customer->getId()) {
            return $proceed($customer, $passwordHash);
        }

        $previousData = $subject->getById($customer->getId());

        $result = $proceed($customer, $passwordHash);

        $getCustomAttribute = $result->getCustomAttribute('is_approved');
        if (!$getCustomAttribute) {
            return $result;
        }

        $valuePrevious = $this->helperData->getValueOfAttrApproved($previousData->getCustomAttribute('is_approved'));
        $value = $this->helperData->getValueOfAttrApproved($getCustomAttribute);
        $emailType = null;

        if ($value == AttributeOptions::APPROVED && ($valuePrevious == AttributeOptions::NOTAPPROVE || $valuePrevious == AttributeOptions::PENDING)) {
            $this->helperData->emailApprovalAction($result, 'approve');
        } else if ($value == AttributeOptions::NOTAPPROVE && (in_array($valuePrevious, [AttributeOptions::APPROVED, AttributeOptions::PENDING, null]))) {
            $this->helperData->emailApprovalAction($result, 'not_approve');
        } else if ($value == AttributeOptions::PENDING && $valuePrevious == null) {
            $this->helperData->emailApprovalAction($result, 'success');
        }

        return $result;
    }
}
