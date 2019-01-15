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
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset\Repository as AssetFile;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Magento\Framework\UrlInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AttributeMetadataDataProvider;

/**
 * Class Data
 * @package Mageplaza\CustomerApproval\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpcustomerapproval';
    const XML_PATH_EMAIL     = 'email';

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var HttpContext
     */
    protected $_httpContext;

    /**
     * @var AssetFile
     */
    protected $_assetRepo;

    /**
     * @var Http
     */
    protected $_requestHttp;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var TransportBuilder
     */
    protected $customerRepositoryInterface;

    /**
     * @var TransportBuilder
     */
    protected $attributeMetadata;

    /**
     * Data constructor.
     *
     * @param Context                       $context
     * @param ObjectManagerInterface        $objectManager
     * @param StoreManagerInterface         $storeManager
     * @param CustomerSession               $customerSession
     * @param HttpContext                   $httpContext
     * @param AssetFile                     $assetRepo
     * @param Http                          $requestHttp
     * @param TransportBuilder              $transportBuilder
     * @param CustomerRepositoryInterface   $customerRepositoryInterface
     * @param AttributeMetadataDataProvider $attributeMetadata
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        HttpContext $httpContext,
        AssetFile $assetRepo,
        Http $requestHttp,
        TransportBuilder $transportBuilder,
        CustomerRepositoryInterface $customerRepositoryInterface,
        AttributeMetadataDataProvider $attributeMetadata
    )
    {
        $this->_customerSession            = $customerSession;
        $this->_httpContext                = $httpContext;
        $this->_assetRepo                  = $assetRepo;
        $this->_requestHttp                = $requestHttp;
        $this->transportBuilder            = $transportBuilder;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->attributeMetadata           = $attributeMetadata;
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
     * @param $customerId
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIsApproved($customerId)
    {
        $customer = $this->getCustomerById($customerId);
        #set default value for customer attribute when is_approved null
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerObj   = $objectManager->create('Magento\Customer\Model\Customer')->getCollection();
        foreach ($customerObj as $customerObjdata) {
            $customermodel = $objectManager->create('Magento\Customer\Model\Customer');
            $customerData  = $customermodel->getDataModel();
            #if is_approved is null
            if ($customerData->getCustomAttribute('is_approved') == null) {
                $customerData->setId($customerObjdata->getData('entity_id'));
                $customerData->setCustomAttribute('is_approved', 'pending');
                $customermodel->updateData($customerData);

                $customerResource = $objectManager->create('\Magento\Customer\Model\ResourceModel\CustomerFactory')->create();
                $customerResource->saveAttribute($customermodel, 'is_approved');
            }
        }
        $customerAttributeData = $customer->getCustomAttribute('is_approved')->getValue();

        return $customerAttributeData;
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
        return $this->_httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP);
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
        return $this->getModuleConfig('admin_notification_email/sendto', $storeId);
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
     * @param $sendTo
     * @param $name
     * @param $email
     * @param $emailTemplate
     * @param $storeId
     *
     * @return bool
     */
    public function sendMail($sendTo, $name, $email, $emailTemplate, $storeId)
    {
        try {
            $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions([
                    'area'  => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars([
                    'name'  => $name,
                    'email' => $email,
                ])
                ->setFrom($this->getEmailSenderConfig($storeId))
                ->addTo($sendTo);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();

            return true;
        } catch (\Magento\Framework\Exception\MailException $e) {
            $this->_logger->critical($e->getLogMessage());
        }

        return false;
    }
}
