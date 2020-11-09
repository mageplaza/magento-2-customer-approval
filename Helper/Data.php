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

namespace Mageplaza\CustomerApproval\Helper;

use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeAction;

/**
 * Class Data
 *
 * @package Mageplaza\CustomerApproval\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpcustomerapproval';
    const XML_PATH_EMAIL = 'email';

    /**
     * @var HttpContext
     */
    protected $_httpContext;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var AttributeOptions
     */
    private $attributeOptions;

    /**
     * @var CustomerViewHelper
     */
    protected $customerViewHelper;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param HttpContext $httpContext
     * @param TransportBuilder $transportBuilder
     * @param AttributeOptions $attributeOptions
     * @param CustomerViewHelper $customerViewHelper
     * @param CustomerRegistry $customerRegistry
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        HttpContext $httpContext,
        TransportBuilder $transportBuilder,
        AttributeOptions $attributeOptions,
        CustomerViewHelper $customerViewHelper,
        CustomerRegistry $customerRegistry
    ) {
        $this->_httpContext = $httpContext;
        $this->transportBuilder = $transportBuilder;
        $this->attributeOptions = $attributeOptions;
        $this->customerViewHelper = $customerViewHelper;
        $this->customerRegistry = $customerRegistry;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param $customerId
     *
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomerById($customerId)
    {
        $customerModel = $this->customerRegistry->retrieve($customerId);

        return $customerModel->getDataModel();
    }

    /**
     * @param int|null $customerId
     *
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getIsApproved($customerId)
    {
        $isApprovedObject = $this->getCustomerById($customerId)
            ->getCustomAttribute('is_approved');
        if (!$isApprovedObject || !$isApprovedObject->getValue()) {
            $this->approvalAction($customerId, AttributeOptions::APPROVED);

            return AttributeOptions::APPROVED;
        }

        return $isApprovedObject->getValue();
    }

    /**
     * @param $status
     *
     * @return string
     */
    public function getApprovalLabel($status)
    {
        $options = $this->attributeOptions->toArray();
        if (!array_key_exists($status, $options)) {
            return '';
        }

        return $options[$status];
    }

    /**
     * @param $customerId
     * @param $typeAction
     *
     * @throws Exception
     */
    public function approvalCustomerById($customerId, $typeAction = TypeAction::OTHER)
    {
        $customer = $this->customerRegistry->retrieve($customerId);
        $this->approvalAction($customer, AttributeOptions::APPROVED);
        // send email
        if ((!$this->getAutoApproveConfig() && !$this->isAdmin()) || $typeAction != TypeAction::OTHER) {
            $this->emailApprovalAction($customer, 'approve');
        }
    }

    /**
     * @param int $customerId
     *
     * @throws Exception
     */
    public function notApprovalCustomerById($customerId)
    {
        $customer = $this->customerRegistry->retrieve($customerId);
        $this->approvalAction($customer, AttributeOptions::NOTAPPROVE);
        // send email
        $this->emailApprovalAction($customer, 'not_approve');
    }

    /**
     * @param Customer|int $customer
     * @param string $typeApproval
     *
     * @throws Exception
     */
    public function approvalAction($customer, $typeApproval)
    {
        if (is_int($customer)) {
            $customer = $this->customerRegistry->retrieve($customer);
        }

        if (!$customer instanceof Customer) {
            throw new NoSuchEntityException(__('Customer does not exist.'));
        }

        $customerData = $customer->getDataModel();
        $attribute = $customerData->getCustomAttribute('is_approved');
        if ($attribute && $attribute->getValue() != $typeApproval) {
            $customerData->setId($customer->getId());
            $customerData->setCustomAttribute('is_approved', $typeApproval);
            $customer->updateData($customerData);
            $customer->save();
        }
    }

    /**
     * @param int $customerId
     * @param bool $actionRegister
     *
     * @throws Exception
     */
    public function setApprovePendingById($customerId, $actionRegister)
    {
        if ($this->getIsApproved($customerId) != AttributeOptions::PENDING) {
            $customer = $this->customerRegistry->retrieve($customerId);
            $customerData = $customer->getDataModel();

            $customerData->setId($customerId);
            $customerData->setCustomAttribute('is_approved', AttributeOptions::PENDING);
            $customer->updateData($customerData);
            $customer->save();

            if ($actionRegister) {
                $this->emailApprovalAction($customer, 'success');
            }
        }
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
     * @param $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function getEmailEnable($type, $storeId = null)
    {
        return $this->getModuleConfig(
            'customer_notification_email/customer_' . $type . '_email/enabled',
            $storeId
        );
    }

    /**
     * @param $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function getEmailTemplate($type, $storeId = null)
    {
        return $this->getModuleConfig(
            'customer_notification_email/customer_' . $type . '_email/template',
            $storeId
        );
    }

    /**
     * @param $customer
     * @param $emailType
     *
     * @throws NoSuchEntityException
     */
    public function emailApprovalAction($customer, $emailType)
    {
        $storeId = $customer->getStoreId();
        $sendTo = $customer->getEmail();
        $sender = $this->getSenderCustomer();
        if ($this->getAutoApproveConfig()) {
            $sender = $this->getConfigValue('customer/create_account/email_identity');
        }

        if ($this->getEmailEnable($emailType)) {
            $template = $this->getEmailTemplate($emailType, $storeId);
            $this->sendMail($sendTo, $customer, $template, $storeId, $sender);
        }
    }

    /**
     * @param $customer
     *
     * @throws NoSuchEntityException
     */
    public function emailNotifyAdmin($customer)
    {
        $storeId = $customer->getStoreId();
        $sender = $this->getSenderAdmin();
        if ($this->getAutoApproveConfig()) {
            $sender = $this->getConfigValue('customer/create_account/email_identity');
        }
        $sendTo = $this->getRecipientsAdmin();
        $sendToArray = explode(',', $sendTo);

        if ($this->getEnabledNoticeAdmin()) {
            // send email notify to admin
            foreach ($sendToArray as $recipient) {
                $this->sendMail(
                    $recipient,
                    $customer,
                    $this->getNoticeAdminTemplate(),
                    $storeId,
                    $sender
                );
            }
        }
    }

    /**
     * @param $sendTo
     * @param $customer
     * @param $emailTemplate
     * @param $storeId
     * @param $sender
     *
     * @return bool
     */
    public function sendMail($sendTo, $customer, $emailTemplate, $storeId, $sender)
    {
        try {
            /** @var Customer $mergedCustomerData */
            $customerEmailData = $this->customerRegistry->retrieve($customer->getId());
            $customerEmailData->setData('name', $customerEmailData->getName());

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions([
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars([
                    'customer' => $customerEmailData
                ])
                ->setFrom($sender)
                ->addTo($sendTo)
                ->getTransport();
            $transport->sendMessage();

            return true;
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
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
     * @param $stringCode
     *
     * @return mixed
     */
    public function getRequestParam($stringCode)
    {
        return $this->_request->getParam($stringCode);
    }

    /**
     * Retrieve cookie manager
     *
     * @return     PhpCookieManager
     * @deprecated 100.1.0
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = ObjectManager::getInstance()->get(PhpCookieManager::class);
        }

        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @return     CookieMetadataFactory
     * @deprecated 100.1.0
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(CookieMetadataFactory::class);
        }

        return $this->cookieMetadataFactory;
    }

    /**
     * @param null $websiteId
     *
     * @return array|mixed
     */
    public function isEnabledForWebsite($websiteId = null)
    {
        return $this->getConfigValue(
            self::CONFIG_MODULE_PATH . '/general/enabled',
            $websiteId,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @param $typeApprove
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function shouldEnableButton($typeApprove)
    {
        if (!$this->getRequestParam('id')) {
            return false;
        }

        $customerId = $this->getRequestParam('id');
        $customer = $this->getCustomerById($customerId);
        $websiteId = $customer->getWebsiteId();

        if (!$this->isEnabledForWebsite($websiteId) || $this->getIsApproved($customerId) == $typeApprove) {
            return false;
        }

        return true;
    }

    /**
     * @param int $customerId
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setPendingCustomer($customerId)
    {
        $cusAttributeData = $this->getIsApproved($customerId);
        if (!$cusAttributeData) {
            $actionRegister = false;
            $this->setApprovePendingById($customerId, $actionRegister);
        }
    }

    /**
     * @return bool
     */
    public function hasCustomerEdit()
    {
        $param = $this->_request->getParams();

        return isset($param['customer']['is_active']);
    }

    /**
     * @throws InputException
     * @throws FailureToSendException
     */
    public function processCookieLogout()
    {
        if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
            $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
            $metadata->setPath('/');
            $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
        }
    }
}
