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

namespace Mageplaza\CustomerApproval\Model;

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\CustomerApproval\Api\ListApproveInterface;
use Mageplaza\CustomerApproval\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class ListApprove
 * @package Mageplaza\CustomerApproval\Model
 */
class ListApprove implements ListApproveInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRespository;

    /**
     * ListApprove constructor.
     *
     * @param Data                        $helperData
     * @param LoggerInterface             $logger
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Data $helperData,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository
    )
    {
        $this->helperData          = $helperData;
        $this->_logger             = $logger;
        $this->customerRespository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function approveCustomer($email)
    {
        try {
            $customer = $this->customerRespository->get($email);
            $customerId = $customer->getId();
            $this->helperData->approvalCustomerById($customerId);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not change approve status for this customer with email %1', $email));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function notApproveCustomer($email)
    {
        try {
            $customer = $this->customerRespository->get($email);
            $customerId = $customer->getId();
            $this->helperData->notApprovalCustomerById($customerId);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not change approve status for this customer with email %1', $email));
        }
    }
}