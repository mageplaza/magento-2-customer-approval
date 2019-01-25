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

namespace Mageplaza\CustomerApproval\Model\ResourceModel;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;

/**
 * Class CustomerRepository
 * @package Mageplaza\CustomerApproval\Model\ResourceModel
 */
class CustomerRepository extends \Magento\Customer\Model\ResourceModel\CustomerRepository
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(\Magento\Customer\Api\Data\CustomerInterface $customer, $passwordHash = null)
    {
        $prevCustomerData = null;
        if ($customer->getId()) {
            $prevCustomerData = $this->getById($customer->getId());
        }
        $customer = $this->imageProcessor->save(
            $customer,
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $prevCustomerData
        );

        $origAddresses = $customer->getAddresses();
        $customer->setAddresses([]);
        $customerData = $this->extensibleDataObjectConverter->toNestedArray(
            $customer,
            [],
            '\Magento\Customer\Api\Data\CustomerInterface'
        );

        $customer->setAddresses($origAddresses);
        $customerModel = $this->customerFactory->create(['data' => $customerData]);
        $storeId = $customerModel->getStoreId();
        if ($storeId === null) {
            $customerModel->setStoreId($this->storeManager->getStore()->getId());
        }
        $customerModel->setId($customer->getId());

        // Need to use attribute set or future updates can cause data loss
        if (!$customerModel->getAttributeSetId()) {
            $customerModel->setAttributeSetId(
                \Magento\Customer\Api\CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER
            );
        }
        // Populate model with secure data
        if ($customer->getId()) {
            $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
            $customerModel->setRpToken($customerSecure->getRpToken());
            $customerModel->setRpTokenCreatedAt($customerSecure->getRpTokenCreatedAt());
            $customerModel->setPasswordHash($customerSecure->getPasswordHash());
            $customerModel->setFailuresNum($customerSecure->getFailuresNum());
            $customerModel->setFirstFailure($customerSecure->getFirstFailure());
            $customerModel->setLockExpires($customerSecure->getLockExpires());
        } else {
            if ($passwordHash) {
                $customerModel->setPasswordHash($passwordHash);
            }
        }

        // If customer email was changed, reset RpToken info
        if ($prevCustomerData
            && $prevCustomerData->getEmail() !== $customerModel->getEmail()
        ) {
            $customerModel->setRpToken(null);
            $customerModel->setRpTokenCreatedAt(null);
        }
        $customerModel->save();
        $this->customerRegistry->push($customerModel);
        $customerId = $customerModel->getId();

        if ($customer->getAddresses() !== null) {
            if ($customer->getId()) {
                $existingAddresses = $this->getById($customer->getId())->getAddresses();
                $getIdFunc = function ($address) {
                    return $address->getId();
                };
                $existingAddressIds = array_map($getIdFunc, $existingAddresses);
            } else {
                $existingAddressIds = [];
            }

            $savedAddressIds = [];
            foreach ($customer->getAddresses() as $address) {
                $address->setCustomerId($customerId)
                    ->setRegion($address->getRegion());
                $this->addressRepository->save($address);
                if ($address->getId()) {
                    $savedAddressIds[] = $address->getId();
                }
            }

            $addressIdsToDelete = array_diff($existingAddressIds, $savedAddressIds);
            foreach ($addressIdsToDelete as $addressId) {
                $this->addressRepository->deleteById($addressId);
            }
        }

        $savedCustomer = $this->get($customer->getEmail(), $customer->getWebsiteId());
        $this->eventManager->dispatch(
            'customer_save_after_data_object',
            ['customer_data_object' => $savedCustomer, 'orig_customer_data_object' => $prevCustomerData]
        );
        return $savedCustomer;
    }
}