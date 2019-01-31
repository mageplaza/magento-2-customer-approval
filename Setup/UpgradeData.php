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

namespace Mageplaza\CustomerApproval\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Cms\Model\PageFactory;

/**
 * Class UpgradeData
 * @package Mageplaza\CustomerApproval\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * UpgradeData constructor.
     *
     * @param PageFactory $pageFactory
     */
    public function __construct(PageFactory $pageFactory)
    {
        $this->_pageFactory = $pageFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $html = '<h1>Welcome</h1><br/>
                <p>Your account has been created and is pending approval. We will notify you via email when your account is approved.</p>
                <p>You will not be able to login until your account has been approved.</p>';
        if (version_compare($context->getVersion(), '1.1') < 0) {
            $page = $this->_pageFactory->create();
            $page->setTitle('Not Approve Customer Page')
                ->setIdentifier('not-approve-customer')
                ->setIsActive(true)
                ->setPageLayout('1column')
                ->setStores([0])
                ->setContent($html)
                ->save();
        }

        $setup->endSetup();
    }
}
