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

namespace Mageplaza\CustomerApproval\Helper;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Data
 * @package Mageplaza\CustomerApproval\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpcustomerapproval';
    const XML_PATH_EMAIL     = 'email';

    /**
     * @var HttpContext
     */
    protected $_httpContext;

    /**
     * @var Http
     */
    protected $_requestHttp;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Data constructor.
     *
     * @param Context                     $context
     * @param ObjectManagerInterface      $objectManager
     * @param StoreManagerInterface       $storeManager
     * @param HttpContext                 $httpContext
     * @param Http                        $requestHttp
     * @param TransportBuilder            $transportBuilder
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param Customer                    $customer
     * @param CustomerFactory             $customerFactory
     * @param ManagerInterface            $messageManager
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        HttpContext $httpContext,
        Http $requestHttp,
        TransportBuilder $transportBuilder,
        CustomerRepositoryInterface $customerRepositoryInterface,
        Customer $customer,
        CustomerFactory $customerFactory,
        ManagerInterface $messageManager
    )
    {
        $this->_httpContext                = $httpContext;
        $this->_requestHttp                = $requestHttp;
        $this->transportBuilder            = $transportBuilder;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customer                    = $customer;
        $this->customerFactory             = $customerFactory;
        $this->messageManager              = $messageManager;
        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @return bool
     */
    public function isCustomerLogedIn()
    {
        return $this->_httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * @param $customerId
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerById($customerId)
    {
        return $this->customerRepositoryInterface->getById($customerId);
    }

    /**
     * @param $CusEmail
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerByEmail($CusEmail)
    {
        return $this->customerRepositoryInterface->get($CusEmail);
    }

    /**
     * @param $customerId
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIsApproved($customerId)
    {
        $value            = null;
        $customer         = $this->getCustomerById($customerId);
        $isApprovedObject = $customer->getCustomAttribute('is_approved');
        if (!$isApprovedObject) {
            return null;
        }
        $isApprovedObjectArray = $isApprovedObject->__toArray();
        $attributeCode         = $isApprovedObjectArray['attribute_code'];
        if ($attributeCode == 'is_approved') {
            $value = $isApprovedObjectArray['value'];
        }

        return $value;
    }

    /**
     * @param $isApprovedObject
     *
     * @return null
     */
    public function getValueOfAttrApproved($isApprovedObject)
    {
        if (!$isApprovedObject) {
            return null;
        }
        $value            = null;
        $isApprovedObject = $isApprovedObject->__toArray();
        $attributeCode    = $isApprovedObject['attribute_code'];
        if ($attributeCode == 'is_approved') {
            $value = $isApprovedObject['value'];
        }

        return $value;
    }

    /**
     * @param $customerId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function approvalCustomerById($customerId)
    {
        $typeApproval      = AttributeOptions::APPROVED;
        $enableSendEmail   = $this->getEnabledApproveEmail();
        $typeTemplateEmail = $this->getApproveTemplate();
        $customer     = $this->customerFactory->create()->load($customerId);
        $this->approvalAction($customerId, $typeApproval);
        #send email
        if(!$this->getAutoApproveConfig()){
            $this->emailApprovalAction($customer, $enableSendEmail, $typeTemplateEmail);
        }
    }

    /**
     * @param $customerId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function notApprovalCustomerById($customerId)
    {
        $typeApproval      = AttributeOptions::NOTAPPROVE;
        $enableSendEmail   = $this->getEnabledNotApproveEmail();
        $typeTemplateEmail = $this->getNotApproveTemplate();
        $customer     = $this->customerFactory->create()->load($customerId);
        $this->approvalAction($customerId, $typeApproval);
        #send email
        $this->emailApprovalAction($customer, $enableSendEmail, $typeTemplateEmail);
    }

    /**
     * @param $customerId
     * @param $typeApproval
     *
     * @throws \Exception
     */
    public function approvalAction($customerId, $typeApproval)
    {
        $customer     = $this->customerFactory->create()->load($customerId);
        $customerData = $customer->getDataModel();
        if ($this->getValueOfAttrApproved($customerData->getCustomAttribute('is_approved')) != $typeApproval) {
            $customerData->setId($customerId);
            $customerData->setCustomAttribute('is_approved', $typeApproval);
            $customer->updateData($customerData);
            $customer->save();
        }
    }

    /**
     * @param $customer
     * @param $enableSendEmail
     * @param $typeTemplateEmail
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function emailApprovalAction($customer, $enableSendEmail, $typeTemplateEmail)
    {
        $storeId = $this->getStoreId();
        $sendTo  = $customer->getEmail();
        $sender  = $this->getSenderCustomer();
        if ($this->getAutoApproveConfig()) {
            $sender = $this->getConfigValue('customer/create_account/email_identity');
        }
        $loginurl = $this->getLoginUrl();

        if ($enableSendEmail) {
            try {
                $this->sendMail(
                    $sendTo,
                    $customer,
                    $loginurl,
                    $typeTemplateEmail,
                    $storeId,
                    $sender);
            } catch (\Exception $e) {
                if ($e->getMessage()) {
                    $this->messageManager->ExceptionMessage($e, __($e->getMessage()));
                }
            }
        }
    }

    /**
     * @param $customerId
     * @param $actionRegister
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setApprovePendingById($customerId, $actionRegister)
    {
        $customer     = null;
        $customer     = $this->customer->load($customerId);
        $customerData = $customer->getDataModel();
        if ($this->getValueOfAttrApproved($customerData->getCustomAttribute('is_approved')) != AttributeOptions::PENDING) {
            $customerData->setId($customerId);
            $customerData->setCustomAttribute('is_approved', AttributeOptions::PENDING);
            $customer->updateData($customerData);
            $customer->save();
        }
        if ($actionRegister) {
            $enableSendEmail   = $this->getEnabledSuccessEmail();
            $typeTemplateEmail = $this->getSuccessTemplate();
            $this->emailApprovalAction($customer, $enableSendEmail, $typeTemplateEmail);
        }
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * @return bool
     */
    public function isCustomerApprovalEnabled()
    {
        return $this->isEnabled();
    }

    /**
     * @return mixed|null
     */
    public function getCustomerGroupId()
    {
        return $this->_httpContext->getValue(CustomerContext::CONTEXT_GROUP);
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->_requestHttp->getRouteName();
    }

    /**
     * @return string
     */
    public function getFullAction()
    {
        return $this->_requestHttp->getFullActionName();
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getEnabledNoticeAdmin($storeId = null)
    {
        return $this->getModuleConfig('admin_notification_email/enabled', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getNoticeAdminTemplate($storeId = null)
    {
        return $this->getModuleConfig('admin_notification_email/template', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getSenderAdmin($storeId = null)
    {
        return $this->getModuleConfig('admin_notification_email/sender', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getRecipientsAdmin($storeId = null)
    {
        return preg_replace('/\s+/', '', $this->getModuleConfig('admin_notification_email/sendto', $storeId));
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getSenderCustomer($storeId = null)
    {
        return $this->getModuleConfig('customer_notification_email/sender', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getEnabledSuccessEmail($storeId = null)
    {
        return $this->getModuleConfig('customer_notification_email/customer_success_email/enabled', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getSuccessTemplate($storeId = null)
    {
        return $this->getModuleConfig('customer_notification_email/customer_success_email/template', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getEnabledApproveEmail($storeId = null)
    {
        return $this->getModuleConfig('customer_notification_email/customer_approve_email/enabled', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getApproveTemplate($storeId = null)
    {
        return $this->getModuleConfig('customer_notification_email/customer_approve_email/template', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getEnabledNotApproveEmail($storeId = null)
    {
        return $this->getModuleConfig('customer_notification_email/customer_not_approve_email/enabled', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getNotApproveTemplate($storeId = null)
    {
        return $this->getModuleConfig('customer_notification_email/customer_not_approve_email/template', $storeId);
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->_getUrl('customer/account/login');
    }

    /**
     * @param $sendTo
     * @param $customer
     * @param $loginPath
     * @param $emailTemplate
     * @param $storeId
     * @param $sender
     *
     * @return bool
     */
    public function sendMail($sendTo, $customer, $loginPath, $emailTemplate, $storeId, $sender)
    {
        try {
            $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions([
                    'area'  => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars([
                    'firstname' => $customer->getFirstname(),
                    'lastname'  => $customer->getLastname(),
                    'email'     => $customer->getEmail(),
                    'loginurl'  => $loginPath,
                ])
                ->setFrom($sender)
                ->addTo($sendTo);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();

            return true;
        } catch (\Magento\Framework\Exception\MailException $e) {
            $this->_logger->critical($e->getLogMessage());
        }

        return false;
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getAutoApproveConfig($storeId = null)
    {
        return $this->getConfigGeneral('auto_approve', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getMessageAfterRegister($storeId = null)
    {
        return $this->getConfigGeneral('message_after_register', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getTypeNotApprove($storeId = null)
    {
        return $this->getConfigGeneral('type_not_approve', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getErrorMessage($storeId = null)
    {
        return $this->getConfigGeneral('error_message', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getCmsRedirectPage($storeId = null)
    {
        return $this->getConfigGeneral('redirect_cms_page', $storeId);
    }

    /**
     * @param $path
     * @param $param
     *
     * @return string
     */
    public function getUrl($path, $param)
    {
        return $this->_getUrl($path, $param);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrlDashboard()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * @param $stringCode
     *
     * @return mixed
     */
    public function getRequestParam($stringCode)
    {
        return $this->_request->getParam($stringCode);
    }

    /**
     * @param $customer
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function emailNotifyAdmin($customer)
    {
        $storeId     = $this->getStoreId();
        $loginurl    = $this->getLoginUrl();
        $sender      = $this->getSenderAdmin();
        $sendTo      = $this->getRecipientsAdmin();
        $sendToArray = explode(',', $sendTo);

        if ($this->getEnabledNoticeAdmin()) {
            #send email notify to admin
            foreach ($sendToArray as $recept) {
                $this->sendMail(
                    $recept,
                    $customer,
                    $loginurl,
                    $this->getNoticeAdminTemplate(),
                    $storeId,
                    $sender);
            }
        }
    }
}
