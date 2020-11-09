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

namespace Mageplaza\CustomerApproval\Console\Command;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Approve
 *
 * @package Mageplaza\CustomerApproval\Console\Command
 */
class Reindex extends Command
{
    const IS_APPROVED = 'is_approved';

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Approve constructor.
     *
     * @param Customer $customer
     * @param State $appState
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param ResourceConnection $resourceConnection
     * @param null $name
     */
    public function __construct(
        Customer $customer,
        State $appState,
        CustomerRepositoryInterface $customerRepositoryInterface,
        ResourceConnection $resourceConnection,
        $name = null
    ) {
        $this->customer = $customer;
        $this->appState = $appState;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->resourceConnection = $resourceConnection;

        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('customer:reindex')
            ->setDescription('Reindex customer account');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->getAreaCode();
        } catch (LocalizedException $e) {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        }

        $resource = $this->resourceConnection;
        $connection = $resource->getConnection();
        $customerGridTable = $resource->getTableName('customer_grid_flat');
        $customerEntityTextTable = $resource->getTableName('customer_entity_text');
        $attributeId = $this->customer->getAttribute(self::IS_APPROVED)->getId();

        $select = $connection->select()
            ->from($customerEntityTextTable, ['entity_id'])
            ->where('attribute_id = ?', $attributeId)
            ->where('value = ?', AttributeOptions::NEW_STATUS);
        $customerIds = $connection->fetchCol($select);

        if ($customerIds) {
            foreach ($customerIds as $id) {
                $connection->update(
                    $customerEntityTextTable,
                    ['value' => AttributeOptions::APPROVED],
                    ['entity_id = ?' => $id]
                );

                $connection->update(
                    $customerGridTable,
                    [self::IS_APPROVED => AttributeOptions::APPROVED],
                    ['entity_id = ?' => $id]
                );
            }
        }

        $output->writeln('');
        $output->writeln('Customer account has reindex successfully!');
    }
}
