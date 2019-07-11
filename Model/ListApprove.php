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

namespace Mageplaza\CustomerApproval\Model;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\CustomerApproval\Api\ListApproveInterface;
use Mageplaza\CustomerApproval\Helper\Data;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeAction;

/**
 * Class ListApprove
 *
 * @package Mageplaza\CustomerApproval\Model
 */
class ListApprove implements ListApproveInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * ListApprove constructor.
     *
     * @param Data $helperData
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Data $helperData,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->helperData = $helperData;
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function approveCustomer($email)
    {
        try {
            $customer = $this->customerRepository->get($email);
            if (!$this->helperData->isEnabledForWebsite($customer->getWebsiteId())) {
                throw new LocalizedException(__('Module is not enabled for the website of this customer'));
            }

            $customerId = $customer->getId();
            if ($this->helperData->getIsApproved($customerId) != AttributeOptions::APPROVED) {
                $this->helperData->approvalCustomerById($customerId, TypeAction::API);
            }
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function notApproveCustomer($email)
    {
        try {
            $customer = $this->customerRepository->get($email);
            if (!$this->helperData->isEnabledForWebsite($customer->getWebsiteId())) {
                throw new LocalizedException(__('Module is not enabled for the website of this customer'));
            }

            $customerId = $customer->getId();
            if ($this->helperData->getIsApproved($customerId) != AttributeOptions::NOTAPPROVE) {
                $this->helperData->notApprovalCustomerById($customerId);
            }
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return true;
    }
}
