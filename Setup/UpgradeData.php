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

namespace Mageplaza\CustomerApproval\Setup;

use Exception;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;

class UpgradeData implements UpgradeDataInterface
{
    const IS_APPROVED = 'is_approved';

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var Config
     */
    protected $eavConfig;

    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        IndexerRegistry $indexerRegistry,
        Config $eavConfig,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->eavConfig = $eavConfig;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();

            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

            $customerSetup->removeAttribute(Customer::ENTITY, self::IS_APPROVED);
            /** @var CustomerSetup $customerSetup */
            $customerSetup->addAttribute(Customer::ENTITY, self::IS_APPROVED, [
                'type' => 'text',
                'length' => 255,
                'label' => 'Approval Status',
                'input' => 'select',
                'source' => AttributeOptions::class,
                'required' => false,
                'default' => AttributeOptions::NEW_STATUS,
                'visible' => true,
                'user_defined' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'sort_order' => 210,
                'position' => 999,
                'system' => false,
            ]);

            $attribute = $customerSetup->getEavConfig()
                ->getAttribute(Customer::ENTITY, self::IS_APPROVED)
                ->addData([
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => ['checkout_register', 'adminhtml_checkout'],
                ]);
            $attribute->save();
            $this->initApprovedForAllCustomer($setup, $attribute->getId());

            $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
            $indexer->reindexAll();
            $this->eavConfig->clear();

            $setup->endSetup();
        }
    }

    /**
     * @param $setup
     * @param $attributeId
     */
    private function initApprovedForAllCustomer($setup, $attributeId)
    {
        $customerEntityTable = $setup->getTable('customer_entity');
        $customerEntityTextTable = $setup->getTable('customer_entity_text');
        $data = [];
        $connection = $setup->getConnection();

        $check = $connection->select()
            ->from($customerEntityTextTable, ['entity_id'])
            ->where('attribute_id = ?', $attributeId);
        $count = count($connection->fetchCol($check));

        if ($count === 0) {
            $select = $connection->select()->from($customerEntityTable, ['entity_id']);
            $customerIds = $connection->fetchCol($select);
            foreach ($customerIds as $id) {
                $data[] = [
                    'attribute_id' => $attributeId,
                    'entity_id' => $id,
                    'value' => AttributeOptions::APPROVED
                ];

                if (sizeof($data) >= 1000) {
                    $connection->insertMultiple($customerEntityTextTable, $data);
                    $data = [];
                }
            }

            if (!empty($data)) {
                $connection->insertMultiple($customerEntityTextTable, $data);
            }
        }
    }
}
