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

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class IsApproved
 * @package Mageplaza\CustomerApproval\Ui\Component\Listing\Columns
 */
class IsApproved extends Column
{
    /**
     * @var DateTime
     */
    protected $date;

    /**
     * State constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param DateTime           $date
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        DateTime $date,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->date       = $date;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
//                if ($item[$this->getData('name')] == 'pending') {
//                    $item[$this->getData('name')] = 'Pending';
//                } elseif ($item[$this->getData('name')] == 'approve') {
//                    $item[$this->getData('name')] = 'Approve';
//                } elseif ($item[$this->getData('name')] == 'notapprove') {
//                    $item[$this->getData('name')] = 'Not Approve';
//                }
                \Zend_Debug::dump($item);
                die();
            }
        }

        return $dataSource;
    }
}
