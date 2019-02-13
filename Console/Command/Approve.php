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
use Magento\Framework\App\State;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeAction;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Approve
 *
 * @package Mageplaza\CustomerApproval\Console\Command
 */
class Approve extends Command
{

    const KEY_EMAIL    = 'customer-email';

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
     * @var HelperData
     */
    protected $helperData;

    /**
     * Approve constructor.
     *
     * @param Customer $customer
     * @param State $appState
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param HelperData $helperData
     * @param null $name
     */
    public function __construct(
        Customer $customer,
        State $appState,
        CustomerRepositoryInterface $customerRepositoryInterface,
        HelperData $helperData,
        $name = null
    ) {
        $this->customer                    = $customer;
        $this->appState                    = $appState;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->helperData                  = $helperData;

        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('customer:approve')
            ->setDescription('Approve customer account');

        $this->addArgument(self::KEY_EMAIL, 1, 'customer email');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->getAreaCode();
        } catch (\Exception $e) {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        }
        if (!$this->helperData->isEnabled()) {
            return null;
        }
        $customerId    = null;
        $emailCustomer = $input->getArgument(self::KEY_EMAIL);
        if ($emailCustomer) {
            $customer   = $this->customerRepositoryInterface->get($emailCustomer);
            $customerId = $customer->getId();
        }
        // approval customer
        if ($customerId) {
            if ($this->helperData->getIsApproved($customerId) != AttributeOptions::APPROVED) {
                $this->helperData->approvalCustomerById($customerId, TypeAction::COMMAND);
            }
            // write log
            $output->writeln('');
            $output->writeln('Approve customer account successfully!');
        }
    }
}
