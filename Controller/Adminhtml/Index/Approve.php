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

namespace Mageplaza\CustomerApproval\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mageplaza\CustomerApproval\Helper\Data;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeAction;

/**
 * Class Approve
 *
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
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Data $helper
    ) {
        $this->helperData = $helper;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/index');
        if ($this->helperData->isEnabled()) {
            $customerId    = (int) $this->getRequest()->getParam('customer_id', 0);
            $approveStatus = $this->getRequest()->getParam('approve_status');

            if (!$customerId) {
                return $resultRedirect;
            }

            try {
                // approve customer account
                if ($approveStatus == AttributeOptions::APPROVED) {
                    if ($customerId) {
                        $this->helperData->approvalCustomerById($customerId, TypeAction::OTHER);
                        $this->messageManager->addSuccessMessage(__('Customer account has approved!'));
                    }
                } else {
                    if ($customerId) {
                        $this->helperData->notApprovalCustomerById($customerId);
                        $this->messageManager->addSuccessMessage(__('Customer account has not approved!'));
                    }
                }
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __($exception->getMessage())
                );
            }

            $resultRedirect->setPath(
                'customer/*/edit',
                ['id' => $customerId, '_current' => true]
            );

            return $resultRedirect;
        }

        return $resultRedirect;
    }
}
