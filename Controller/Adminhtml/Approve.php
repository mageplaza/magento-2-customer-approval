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

namespace Mageplaza\CustomerApproval\Controller\Adminhtml;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;

class Approve extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Reset password handler
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $customerId = (int)$this->getRequest()->getParam('customer_id', 0);
        if (!$customerId) {
            $resultRedirect->setPath('customer/index');
            return $resultRedirect;
        }

        try {
            $customer = $this->_customerRepository->getById($customerId);
            $this->customerAccountManagement->initiatePasswordReset(
                $customer->getEmail(),
                \Magento\Customer\Model\AccountManagement::EMAIL_REMINDER,
                $customer->getWebsiteId()
            );
            $this->messageManager->addSuccess(__('Ok vao day roi day'));
        } catch (NoSuchEntityException $exception) {
            $resultRedirect->setPath('customer/index');
            return $resultRedirect;
        } catch (\Magento\Framework\Validator\Exception $exception) {
            $messages = $exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR);
            if (!count($messages)) {
                $messages = $exception->getMessage();
            }
            $this->_addSessionErrorMessages($messages);
        } catch (SecurityViolationException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addException(
                $exception,
                __('Something went wrong while resetting customer password.')
            );
        }
        $resultRedirect->setPath(
            'customer/*/edit',
            ['id' => $customerId, '_current' => true]
        );
        return $resultRedirect;
    }
}