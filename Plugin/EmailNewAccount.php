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

namespace Mageplaza\CustomerApproval\Plugin;

use Mageplaza\CustomerApproval\Helper\Data as HelperData;

/**
 * Class EmailNewAccount
 * @package Mageplaza\CustomerApproval\Plugin
 */
class EmailNewAccount
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * CalculatorShipping constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    )
    {
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Customer\Model\Customer $subject
     * @param \Closure                         $proceed
     * @param string                           $type
     * @param string                           $backUrl
     * @param string                           $storeId
     *
     * @return mixed|null
     */
    public function aroundSendNewAccountEmail(\Magento\Customer\Model\Customer $subject, \Closure $proceed, $type = 'registered', $backUrl = '', $storeId = '0')
    {
        $result = $proceed($type, $backUrl, $storeId);
        if (!$this->helperData->isEnabled() || $this->helperData->getAutoApproveConfig()) {
            return $result;
        }else{
            return NULL;
        }
    }
}
