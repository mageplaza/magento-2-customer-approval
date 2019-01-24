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

namespace Mageplaza\CustomerApproval\Console\Command;

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Customer\Model\Customer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Mageplaza\CustomerApproval\Helper\Data;
use Magento\Framework\App\Area;

/**
 * Class NotApprove
 * @package Mageplaza\CustomerApproval\Console\Command
 */
class NotApprove extends Command
{

    const KEY_EMAIL     = 'customer-email';
    const KEY_SENDEMAIL = 'send-email';

    /**
     * @var Data
     */
    protected $data;

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
     * @var State
     */
    private $helperData;

    /**
     * NotApprove constructor.
     *
     * @param Customer                    $customer
     * @param State                       $appState
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param Data                        $helperData
     * @param null                        $name
     */
    public function __construct(
        Customer $customer,
        State $appState,
        CustomerRepositoryInterface $customerRepositoryInterface,
        Data $helperData,
        $name = null
    )
    {
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
        $this->setName('customer:user:notapprove')
            ->setDescription('Not approve customer account')
            ->setDefinition($this->getOptionsList());

        parent::configure();
    }

    /**
     * {@inheritdoc}
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
        $customer   = $this->customerRepositoryInterface->get($input->getOption(self::KEY_EMAIL));
        $customerId = $customer->getId();
        #not approval customer
        $this->helperData->notApprovalCustomerById($customerId);

        #write log
        $output->writeln('');
        $output->writeln('<info>Customer account has not approved!</info>');

        return $this;
    }

    /**
     * @return array
     */
    protected function getOptionsList()
    {
        return [
            new InputOption(self::KEY_EMAIL, null, InputOption::VALUE_REQUIRED, '(Required) Customer email')
        ];
    }
}
