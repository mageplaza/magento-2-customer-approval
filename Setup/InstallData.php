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

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\PageFactory;

/**
 * Class InstallData
 * @package Mageplaza\CustomerApproval\Setup
 */
class InstallData implements InstallDataInterface
{
    const IS_APPROVED = 'is_approved';

    protected $customerSetupFactory;
    private $attributeSetFactory;

    /**
     * @var PageFactory
     */
    protected $_pageFactory;


    /**
     * InstallData constructor.
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory  $attributeSetFactory
     * @param PageFactory          $pageFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        PageFactory $pageFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
        $this->_pageFactory         = $pageFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @throws \Exception
     * @SuppressWarnings(Unused)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet     = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, self::IS_APPROVED, [
            'type'               => 'varchar',
            'label'              => 'Approval Status',
            'input'              => 'text',
            "source"             => "Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions",
            'required'           => false,
            'default'            => 'approved',
            'visible'            => true,
            'user_defined'       => true,
            'is_used_in_grid'    => true,
            'is_visible_in_grid' => true,
            'sort_order'         => 210,
            'position'           => 999,
            'system'             => false,
        ]);

        $is_approved = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, self::IS_APPROVED)
            ->addData([
                'attribute_set_id'   => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms'      => ['checkout_register', 'adminhtml_checkout'],
            ]);

        $is_approved->save();

        # delete cms page not approve if exist
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

        $setup->endSetup();
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