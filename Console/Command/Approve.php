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
use Magento\Framework\App\Area;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;

/**
 * Class Approve
 * @package Mageplaza\CustomerApproval\Console\Command
 */
class Approve extends Command
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
     * @var CustomerRepositoryInterface
     */
    protected $helperData;

    /**
     * Approve constructor.
     *
     * @param Customer                    $customer
     * @param State                       $appState
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param HelperData                  $helperData
     * @param null                        $name
     */
    public function __construct(
        Customer $customer,
        State $appState,
        CustomerRepositoryInterface $customerRepositoryInterface,
        HelperData $helperData,
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
        $this->setName('customer:user:approve')
            ->setDescription('Approve customer account')
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
        #approval customer
        $customer     = $this->customer->load($customerId);
        $customerData = $customer->getDataModel();
        if ($customerData->getCustomAttribute('is_approved') != AttributeOptions::APPROVED) {
            $customerData->setId($customerId);
            $customerData->setCustomAttribute('is_approved', AttributeOptions::APPROVED);
            $customer->updateData($customerData);
            $customer->save();
        }

        $storeId = $this->helperData->getStoreId();
        $sendTo  = $customer->getEmail();
        $sender  = $this->helperData->getSenderCustomer();
        #send emailto customer
        $this->helperData->sendMail(
            $sendTo,
            $customer->getFirstName(),
            $customer->getEmail(),
            $this->helperData->getApproveTemplate(),
            $storeId,
            $sender);

        #write log
        $output->writeln('');
        $output->writeln('<info>Approve customer account successfully!</info>');

    }

    protected function getOptionsList()
    {
        return [
            new InputOption(self::KEY_EMAIL, null, InputOption::VALUE_REQUIRED, '(Required) Customer email')
        ];
    }
}
