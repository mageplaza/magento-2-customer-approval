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

namespace Mageplaza\CustomerApproval\Plugin\Magento\Tax;

use Magento\Customer\Model\Session;
use Magento\Tax\Observer\CustomerLoggedInObserver as CustomerLoggedIn;
use Magento\Framework\Event\Observer;
use Closure;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;

/**
 * Class CustomerLoggedInObserver
 * @package Mageplaza\CustomerApproval\Plugin\Magento\Tax
 */
class CustomerLoggedInObserver
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * CustomerLoggedInObserver constructor.
     * @param HelperData $helperData
     * @param Session $customerSession
     */
    public function __construct(
        HelperData $helperData,
        Session $customerSession
    ) {
        $this->helperData = $helperData;
        $this->_customerSession = $customerSession;
    }

    /**
     * @param CustomerLoggedIn $subject
     * @param Closure $proceed
     * @param Observer $observer
     * @return mixed|void
     */
    public function aroundExecute(CustomerLoggedIn $subject, Closure $proceed, Observer $observer)
    {
        if (!$this->helperData->isEnabled() || $this->_customerSession->isLoggedIn()) {
            return $proceed($observer);
        }

        return;
    }
}
