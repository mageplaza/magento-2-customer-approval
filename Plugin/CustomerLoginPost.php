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

use Magento\Customer\Controller\Account\LoginPost;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

/**
 * Class CustomerLoginPost
 * @package Mageplaza\CustomerApproval\Plugin
 */
class CustomerLoginPost
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var RedirectInterface
     */
    protected $_redirect;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var CustomerUrl
     */
    private $customerUrl;


    /**
     * CustomerLoginPost constructor.
     * @param Validator $formKeyValidator
     * @param Session $customerSession
     * @param RedirectFactory $resultRedirectFactory
     * @param AccountManagementInterface $customerAccountManagement
     * @param PhpCookieManager $cookieMetadataManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        Validator $formKeyValidator,
        Session $customerSession,
        RedirectFactory $resultRedirectFactory,
        AccountManagementInterface $customerAccountManagement,
        PhpCookieManager $cookieMetadataManager,
        CookieMetadataFactory $cookieMetadataFactory,
        AccountRedirect $accountRedirect,
        ScopeConfigInterface $scopeConfig,
        RedirectInterface $_redirect,
        MessageManagerInterface $messageManager,
        CustomerUrl $customerUrl
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->session = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->cookieMetadataManager = $cookieMetadataManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->accountRedirect = $accountRedirect;
        $this->scopeConfig = $scopeConfig;
        $this->_redirect = $_redirect;
        $this->messageManager = $messageManager;
        $this->customerUrl = $customerUrl;
    }

    public function aroundExecute(LoginPost $loginPost, $result)
    {
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($loginPost->getRequest())) {
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        if ($loginPost->getRequest()->isPost()) {
            $login = $loginPost->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $resultAuth = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                    if ($resultAuth instanceof CustomerInterface) {
                        $customer = $resultAuth;
                    } else {
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setPath($resultAuth);
                        return $resultRedirect;
                    }

                    $this->session->setCustomerDataAsLoggedIn($customer);
                    if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                        $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                    }
                    $redirectUrl = $this->accountRedirect->getRedirectCookie();
                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                        $this->accountRedirect->clearRedirectCookie();
                        $resultRedirect = $this->resultRedirectFactory->create();
                        // URL is checked to be internal in $this->_redirect->success()
                        $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                        return $resultRedirect;
                    }
                } catch (EmailNotConfirmedException $e) {
                    $this->messageManager->addComplexErrorMessage(
                        'confirmAccountErrorMessage',
                        ['url' => $this->customerUrl->getEmailConfirmationUrl($login['username'])]
                    );
                    $this->session->setUsername($login['username']);
                } catch (AuthenticationException $e) {
                    $message = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
                } catch (LocalizedException $e) {
                    $message = $e->getMessage();
                } catch (\Exception $e) {
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    $this->messageManager->addErrorMessage(
                        __('An unspecified error occurred. Please contact us for assistance.')
                    );
                } finally {
                    if (isset($message)) {
                        $this->messageManager->addErrorMessage($message);
                        $this->session->setUsername($login['username']);
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('A login and a password are required.'));
            }
        }

        return $this->accountRedirect->getRedirect();

    }

    /**
     * @return PhpCookieManager|mixed
     */
    public function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * @return CookieMetadataFactory|mixed
     */
    public function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * @return ScopeConfigInterface|mixed
     */
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }
}
