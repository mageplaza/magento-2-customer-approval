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

namespace Mageplaza\CustomerApproval\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeNotApprove;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ResponseInterface;
use Magento\Customer\Model\CustomerFactory;

/**
 * Class CustomerLogin
 * @package Mageplaza\CustomerApproval\Observer
 */
class CustomerLogin implements ObserverInterface
{
    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * CustomerLogin constructor.
     *
     * @param HelperData        $helperData
     * @param ManagerInterface  $messageManager
     * @param CustomerSession   $customerSession
     * @param ActionFlag        $actionFlag
     * @param ResponseInterface $response
     * @param CustomerFactory   $customerFactory
     */
    public function __construct(
        HelperData $helperData,
        ManagerInterface $messageManager,
        CustomerSession $customerSession,
        ActionFlag $actionFlag,
        ResponseInterface $response,
        CustomerFactory $customerFactory
    )
    {
        $this->helperData       = $helperData;
        $this->messageManager   = $messageManager;
        $this->_customerSession = $customerSession;
        $this->_actionFlag      = $actionFlag;
        $this->_response        = $response;
        $this->_customerFactory = $customerFactory;
    }

    /**
     * @param Observer $observer
     *
     * @return null|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if ($this->helperData->isEnabled()) {
            $paramsPost = $observer->getEvent()->getRequest()->getParams();
            $emailLogin = null;
            if (isset($paramsPost['login']['username'])) {
                $emailLogin = $paramsPost['login']['username'];
            }
            $customer   = $this->_customerFactory->create()->setWebsiteId(1)->loadByEmail($emailLogin);
            $customerId = null;
            if ($customer->getId()) {
                $customerId = $customer->getId();
            }
            if ($customerId) {
                if ($this->helperData->getIsApproved($customerId) != AttributeOptions::APPROVED) {
                    if ($this->helperData->getTypeNotApprove() == TypeNotApprove::SHOW_ERROR) {
                        #case show error
                        $urlLogin = $this->helperData->getUrl('customer/account/login', ['_secure' => true]);
                        $this->_actionFlag->set('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH, true);
                        $this->_response->setRedirect($urlLogin);

                        $this->messageManager->addErrorMessage(__($this->helperData->getErrorMessage()));
                    } else {
                        #case redirect
                        $cmsRedirect = $this->helperData->getCmsRedirectPage();
                        if ($cmsRedirect == 'home') {
                            $urlRedirect = $this->helperData->getBaseUrlDashboard();
                        } else {
                            $urlRedirect = $this->helperData->getUrl($cmsRedirect, ['_secure' => true]);
                        }
                        $this->_actionFlag->set('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH, true);
                        $this->_response->setRedirect($urlRedirect);
                    }
                }
            }
        }
    }
}