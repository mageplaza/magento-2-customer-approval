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

namespace Mageplaza\CustomerApproval\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mageplaza\CustomerApproval\Helper\Data;
use Magento\Cms\Model\PageFactory;

/**
 * Class Approve
 * @package Mageplaza\CustomerApproval\Controller\Adminhtml\Index
 */
class upgradeData extends Action
{

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * upgradeData constructor.
     *
     * @param Context     $context
     * @param Data        $helper
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        Data $helper,
        PageFactory $pageFactory
    )
    {
        $this->helperData   = $helper;
        $this->_pageFactory = $pageFactory;

        parent::__construct($context);
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $this->deletecmsExist('not-approved');
        $html = '<h1>Welcome</h1><br/>
                <p>Your account has been created and is pending approval. We will notify you via email when your account is approved.</p>
                <p>You will not be able to login until your account has been approved.</p>';

        // create new cms page
        $cmsNotApprove = $this->_pageFactory->create()->load('not-approved');
        if (!$cmsNotApprove->getPageId()) {
            $cmsFactory = $this->_pageFactory->create();
            $cmsFactory->setTitle('Not Approve Customer Page')
                ->setIdentifier('not-approved')
                ->setIsActive(true)
                ->setPageLayout('1column')
                ->setStores([0])
                ->setContent($html)
                ->save();
        } else {
            $cmsNotApprove->setContent($html)->save();
        }

        return $this;
    }

    /**
     * @param $identifier
     *
     * @return $this
     * @throws \Exception
     */
    public function deletecmsExist($identifier)
    {
        $cmsFactory = $this->_pageFactory->create()->load($identifier, 'identifier');
        if ($cmsFactory->getPageId()) {
            $cmsFactory->load($cmsFactory->getPageId())->delete();
            $cmsFactory->save();
        }

        return $this;
    }
}
