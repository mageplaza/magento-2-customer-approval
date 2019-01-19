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

namespace Mageplaza\CustomerApproval\Model;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\CustomerApproval\Api\ListApproveInterface;
use Mageplaza\CustomerApproval\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class ListApprove
 * @package Mageplaza\CustomerApproval\Model
 */
class ListApprove implements ListApproveInterface
{
    /**
     * @var Random
     */
    protected $_mathRandom;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ListApprove constructor.
     *
     * @param Data                  $helperData
     * @param Random                $mathRandom
     * @param TransportBuilder      $transportBuilder
     * @param LoggerInterface       $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $helperData,
        Random $mathRandom,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    )
    {
        $this->helperData        = $helperData;
        $this->_mathRandom       = $mathRandom;
        $this->_transportBuilder = $transportBuilder;
        $this->_logger           = $logger;
        $this->storeManager      = $storeManager;
    }

    /**
     * Get List Approve
     *
     * @return mixed|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getListApprove()
    {
        if (!$this->helperData->isEnabled()) {
            throw new NoSuchEntityException(__('Module Mageplaza_CustomerApproval is not enabled.'));
        }

        try {
            $generateResult = $this->helperData->getListCustomerApprove();
        } catch (\Exception $e) {
            throw new LocalizedException(__('Something went wrong while get list customer approve.'));
        }

        return $generateResult;
    }
}