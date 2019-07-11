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

namespace Mageplaza\CustomerApproval\Plugin;

use Closure;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\EmailNotification;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;

/**
 * Class EmailNewAccount
 *
 * @package Mageplaza\CustomerApproval\Plugin
 */
class EmailNewAccount
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * EmailNewAccount constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param EmailNotification $subject
     * @param Closure $proceed
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int $storeId
     * @param null $sendemailStoreId
     *
     * @return                   mixed|null
     * @SuppressWarnings(Unused)
     */
    public function aroundNewAccount(
        EmailNotification $subject,
        Closure $proceed,
        CustomerInterface $customer,
        $type = EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    ) {
        if (!$this->helperData->isEnabled()
            || $this->helperData->getAutoApproveConfig()
            || (!$this->helperData->hasCustomerEdit() && $this->helperData->isAdmin())
            || $customer->getConfirmation()
        ) {
            return $proceed($customer, $type, $backUrl, $storeId, $sendemailStoreId);
        }

        return null;
    }
}
