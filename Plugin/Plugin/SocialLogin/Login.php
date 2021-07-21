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

namespace Mageplaza\CustomerApproval\Plugin\SocialLogin;

use Magento\Customer\Controller\Account\Confirm;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Mageplaza\SocialLogin\Controller\Social\Login as SocialLogin;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeAction;

/**
 * Class Login
 *
 * @package Mageplaza\CustomerApproval\Plugin\SocialLogin
 */
class Login
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectInterface
     */
    protected $_redirect;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * CustomerConfirm constructor.
     *
     * @param HelperData $helperData
     * @param ManagerInterface $messageManager
     * @param RedirectInterface $redirect
     * @param Session $customerSession
     */
    public function __construct(
        HelperData $helperData,
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        Session $customerSession
    ) {
        $this->helperData = $helperData;
        $this->messageManager = $messageManager;
        $this->_redirect = $redirect;
        $this->_customerSession = $customerSession;
    }

    /**
     * @param SocialLogin $subject
     * @param $result
     * @return mixed
     * @throws FailureToSendException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterCreateCustomer(SocialLogin $subject, $result)
    {
        if (!$this->helperData->isEnabled()) {
            return $result;
        }

        $customerId = $result->getId();
        if ($customerId) {
            $statusCustomer = $this->helperData->getIsApproved($customerId);
            if ($statusCustomer == AttributeOptions::APPROVED) {
                return $result;
            }

            if ($statusCustomer === AttributeOptions::NEW_STATUS) {
                if ($customerId) {
                    $customer = $this->helperData->getCustomerById($customerId);

                    if ($this->helperData->getAutoApproveConfig()) {
                        // case allow auto approve
                        $this->helperData->approvalCustomerById($customerId, TypeAction::OTHER);
                        // send email approve to customer
                        $this->helperData->emailApprovalAction($customer, 'approve');
                    } else {
                        // case not allow auto approve
                        $actionRegister = false;
                        $this->helperData->setApprovePendingById($customerId, $actionRegister);
                        $this->messageManager->addNoticeMessage(__($this->helperData->getMessageAfterRegister()));
                        // send email notify to admin
                        $this->helperData->emailNotifyAdmin($customer);
                        // send email notify to customer
                        $this->helperData->emailApprovalAction($customer, 'success');

                        // processCookieLogout
                        $this->helperData->processCookieLogout();

                        // set redirect url if not approval
                        $redirectUrl = $this->helperData->getCmsRedirectPage();
                        $this->_customerSession->setMpRedirectUrl($redirectUrl);
                    }
                }
            }
        }

        return $result;
    }
}
