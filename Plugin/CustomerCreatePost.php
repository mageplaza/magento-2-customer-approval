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

use Magento\Customer\Controller\Account\CreatePost;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeAction;

/**
 * Class CustomerCreatePost
 *
 * @package Mageplaza\CustomerApproval\Plugin
 */
class CustomerCreatePost
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
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var RedirectInterface
     */
    protected $_redirect;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var ResponseFactory
     */
    private $_response;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * CustomerCreatePost constructor.
     *
     * @param HelperData $helperData
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $resultRedirectFactory
     * @param RedirectInterface $redirect
     * @param Session $customerSession
     * @param ResponseFactory $responseFactory
     * @param Customer $customer
     */
    public function __construct(
        HelperData $helperData,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory,
        RedirectInterface $redirect,
        Session $customerSession,
        ResponseFactory $responseFactory,
        Customer $customer
    ) {
        $this->helperData            = $helperData;
        $this->messageManager        = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->_redirect             = $redirect;
        $this->_customerSession      = $customerSession;
        $this->_response             = $responseFactory;
        $this->customer              = $customer;
    }

    /**
     * @param CreatePost $createPost
     * @param $result
     *
     * @return mixed
     * @throws FailureToSendException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterExecute(CreatePost $createPost, $result)
    {
        if (!$this->helperData->isEnabled()) {
            return $result;
        }
        $request    = $createPost->getRequest();
        $emailPost  = $request->getParam('email');

        $customer = $this->_customerSession->getCustomer();
        if (!$customer->getId() && $emailPost) {
            $customer = $this->customer->load($emailPost, 'email');
        }

        $customerId     = $customer->getId();
        $statusCustomer = $this->helperData->getIsApproved($customerId);

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
                    // force logout customer
                    $this->_customerSession->logout()
                        ->setBeforeAuthUrl($this->_redirect->getRefererUrl())
                        ->setLastCustomerId($customerId);

                    // processCookieLogout
                    $this->helperData->processCookieLogout();

                    // force redirect
                    $url = $this->helperData->getUrl('customer/account/login', ['_secure' => true]);
                    /**
                     * @var Response $response
                     */
                    $response = $this->_response->create();
                    $response->setRedirect($url)->sendResponse();
                }
            }
        }

        return $result;
    }
}
