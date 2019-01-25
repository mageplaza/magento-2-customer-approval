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

namespace Mageplaza\CustomerApproval\Plugin\ResourceModel;

use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class AroundSaveData
 * @package Mageplaza\CustomerApproval\Plugin\ResourceModel
 */
class AroundSaveData
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * AroundSaveData constructor.
     *
     * @param HelperData       $helperData
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        HelperData $helperData,
        ManagerInterface $eventManager
    )
    {
        $this->helperData   = $helperData;
        $this->eventManager = $eventManager;
    }

    /**
     * @param CustomerRepository $subject
     * @param \Closure           $proceed
     * @param CustomerInterface  $customer
     * @param null               $passwordHash
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
    )
    {
        if ($this->helperData->isEnabled()) {
            $prevCustomerOldData  = $subject->getById($customer->getId());
            $result               = $proceed($customer, $passwordHash);
            $savedCustomerNewData = $subject->get($customer->getEmail(), $customer->getWebsiteId());

            $this->eventManager->dispatch(
                'customer_approval_save_data_object',
                [
                    'orig_customer_data_object' => $prevCustomerOldData,
                    'customer_data_object' => $savedCustomerNewData
                ]
            );
        }

        return $result;
    }
}
