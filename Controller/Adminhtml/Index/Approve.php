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

namespace Mageplaza\CustomerApproval\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Mageplaza\CustomerApproval\Helper\Data;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;

/**
 * Class Approve
 * @package Mageplaza\CustomerApproval\Controller\Adminhtml\Index
 */
class Approve extends Action
{

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Approve constructor.
     *
     * @param Context $context
     * @param Data    $helper
     */
    public function __construct(
        Context $context,
        Data $helper
    )
    {
        $this->helperData = $helper;

        parent::__construct($context);
    }

    /**
     * Reset password handler
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if(!$this->helperData->isEnabled()){
            return false;
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $customerId     = (int) $this->getRequest()->getParam('customer_id', 0);
        $approveStatus = $this->getRequest()->getParam('approve_status');

        if (!$customerId) {
            $resultRedirect->setPath('customer/index');

            return $resultRedirect;
        }

        try {
            #approve customer account
            if($approveStatus == AttributeOptions::APPROVED){
                $this->helperData->approvalCustomerById($customerId);
                $this->messageManager->addSuccess(__('Customer account has approved!'));
            }else{
                $this->helperData->notApprovalCustomerById($customerId);
                $this->messageManager->addSuccess(__('Customer account has not approved!'));
            }
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
                __('Something went wrong while approve account.')
            );
        }

        $resultRedirect->setPath(
            'customer/*/edit',
            ['id' => $customerId, '_current' => true]
        );

        return $resultRedirect;
    }
}