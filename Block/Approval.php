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

namespace Mageplaza\CustomerApproval\Block\Product\ProductList;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\CustomerApproval\Helper\Data;

/**
 * Class Approval
 * @package Mageplaza\CustomerApproval\Block\Product\ProductList
 */
class Approval extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * Approval constructor.
     *
     * @param Context  $context
     * @param Registry $registry
     * @param Data     $dataHelper
     * @param array    $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $dataHelper,
        array $data = []
    )
    {
        $this->registry   = $registry;
        $this->dataHelper = $dataHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function getPosition()
    {
        return true;
    }
}
