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

namespace Mageplaza\CustomerApproval\Ui\Component\Listing\Columns;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;

/**
 * Class Approval
 * @package Mageplaza\CustomerApproval\Ui\Component\Listing\Columns
 */
class Approval extends Column
{
    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Approval constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param DateTime $date
     * @param HelperData $helperData
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        DateTime $date,
        HelperData $helperData,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->date       = $date;
        $this->helperData = $helperData;
    }

    /**
     * @param string $key
     * @param null   $index
     *
     * @return mixed|string
     */
    public function getData($key = '', $index = null)
    {
        if ($key == 'config/dataType') {
            return 'text';
        }

        return parent::getData($key, $index);
    }
}
