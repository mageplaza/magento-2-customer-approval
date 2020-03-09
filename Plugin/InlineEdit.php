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

use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Magento\Framework\App\Action\AbstractAction as AbstractAction;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepositoryInterface;

class InlineEdit
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var AbstractAction
     */
    protected $action;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * EmailNewAccount constructor.
     *
     * @param HelperData $helperData
     */

    public function __construct(
        HelperData $helperData,
        AbstractAction $abstractAction,
        CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->helperData = $helperData;
        $this->action = $abstractAction;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function aroundExecute(\Magento\Customer\Controller\Adminhtml\Index\InlineEdit $subject, callable $proceed)
    {
        if ($this->helperData->isEnabled()) {
            $postItems = $this->action->getRequest()->getParam('items', []);

            if (count($postItems) > 0) {
                foreach ($postItems as $key => $value) {
                    $customer = $this->customerRepositoryInterface->getById($key);
                    $customerAttributeData = $customer->__toArray();
                    $is_approved = $customerAttributeData['custom_attributes']['is_approved']['value'];
                    if ($value['is_approved'] !== $is_approved) {
                        $getCustomer = $this->helperData->getCustomerById($key);
                        if ($value['is_approved'] === 'pending') {
                            $this->helperData->emailApprovalAction($getCustomer, 'success');
                        } elseif ($value['is_approved'] === 'notapproved') {
                            $this->helperData->emailApprovalAction($getCustomer, 'not_approve');
                        } else {
                            $this->helperData->emailApprovalAction($getCustomer, 'approve');
                        }
                    }
                }
            }
        }

        return $proceed();
    }
}