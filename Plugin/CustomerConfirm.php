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

use Magento\Customer\Controller\Account\Confirm;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeNotApprove;

/**
 * Class CustomerConfirm
 *
 * @package Mageplaza\CustomerApproval\Plugin
 */
class CustomerConfirm
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
     * @param Confirm $subject
     * @param $result
     *
     * @return mixed
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws FailureToSendException
     */
    public function afterExecute(Confirm $subject, $result)
    {
        if (!$this->helperData->isEnabled()) {
            return $result;
        }

        $customerId = $subject->getRequest()->getParam('id');
        if ($customerId) {
            $isApproved = $this->helperData->getIsApproved($customerId);
            if ($isApproved == AttributeOptions::APPROVED) {
                return $result;
            }

            $urlRedirect = $this->processRedirect(
                $customerId,
                $this->helperData->getUrl($this->helperData->getCmsRedirectPage(), ['_secure' => true])
            );

            return $result->setUrl($urlRedirect);
        }

        return $result;
    }

    /**
     * @param $customerId
     * @param $urlRedirect
     *
     * @return string
     * @throws InputException
     * @throws FailureToSendException
     */
    public function processRedirect($customerId, $urlRedirect)
    {
        if ($this->helperData->getTypeNotApprove() == TypeNotApprove::SHOW_ERROR
            || $this->helperData->getTypeNotApprove() == null
        ) {
            // case show error
            $urlRedirect = $this->helperData->getUrl('customer/account/login', ['_secure' => true]);
            $this->messageManager->addErrorMessage(__($this->helperData->getErrorMessage()));
        }
        $this->_customerSession->logout()
            ->setBeforeAuthUrl($this->_redirect->getRefererUrl())
            ->setLastCustomerId($customerId);

        // processCookieLogout
        $this->helperData->processCookieLogout();

        return $urlRedirect;
    }
}
