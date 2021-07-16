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

namespace Mageplaza\CustomerApproval\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeNotApprove;

/**
 * Class LoginPostRedirect
 * @package Mageplaza\CustomerApproval\Observer
 */
class LoginPostRedirect implements ObserverInterface
{
    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var Session
     */
    private $_customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * LoginPostRedirect constructor.
     * @param HelperData $helperData
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $url
     */
    public function __construct(
        HelperData $helperData,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        UrlInterface $url
    ){
        $this->helperData = $helperData;
        $this->_customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->url = $url;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        if ($this->helperData->getTypeNotApprove() === TypeNotApprove::SHOW_ERROR
            || empty($this->helperData->getTypeNotApprove())
        ){
            return $this;
        }

        $object = $observer->getEvent()->getObject();
        $redirectUrl = $this->_customerSession->getMpRedirectUrl();
        if ($redirectUrl ) {
            $object->setUrl($this->url->getUrl($redirectUrl));
            $this->_customerSession->unsMpRedirectUrl();
        }

        return $this;
    }
}
