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

namespace Mageplaza\CustomerApproval\Plugin\SocialLogin;

use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Mageplaza\SocialLogin\Controller\Social\AbstractSocial as SLController;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Closure;

/**
 * Class AbstractSocial
 *
 * @package Mageplaza\CustomerApproval\Plugin\SocialLogin
 */
class AbstractSocial
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * AbstractSocial constructor.
     * @param HelperData $helperData
     * @param Session $customerSession
     * @param RawFactory $rawFactory
     */
    public function __construct(
        HelperData $helperData,
        Session $customerSession,
        RawFactory $rawFactory
    ) {
        $this->helperData = $helperData;
        $this->_customerSession = $customerSession;
        $this->resultRawFactory = $rawFactory;
    }

    /**
     * @param SLController $subject
     * @param \Closure $proceed
     * @param null $content
     * @return mixed
     */
    public function around_appendJs(SLController $subject, Closure $proceed, $content = null)
    {
        if (!$this->helperData->isEnabled() || $this->helperData->getTypeNotApprove() === 'show_error') {
            return $proceed($content);
        }

        /** @var \Mageplaza\SocialLogin\Helper\Data $slHelper */
        $slHelper = $this->helperData->createObject(\Mageplaza\SocialLogin\Helper\Data::class);

        if (($slHelper->isCheckMode() || $slHelper->requiredMoreInfo()) && $content) {
            return $proceed($content);
        }

        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $redirectUrl = $this->helperData->getUrl(
            $this->helperData->getCmsRedirectPage(),
            ['_secure' => true]
        );

        if (!$this->_customerSession->isLoggedIn()) {
            return $resultRaw->setContents(
                sprintf(
                    "<script>window.opener.socialCallback('%s', window);</script>",
                    $redirectUrl
                )
            );
        }

        return $proceed($content);
    }
}
