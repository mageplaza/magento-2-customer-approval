<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\CustomerApproval\Model\Plugin\Customer;

use Mageplaza\CustomerApproval\Helper\Data;

/**
 * Class DataProvider
 * @package Mageplaza\CustomerApproval\Model\Plugin\Customer
 */
class DataProvider
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * DataProvider constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    )
    {
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Customer\Ui\Component\DataProvider $subject
     * @param                                             $result
     *
     * @return mixed
     * @throws \Exception
     * @SuppressWarnings(Unused)
     */
    public function afterGetData(\Magento\Customer\Ui\Component\DataProvider $subject, $result)
    {
        if (isset($result['items'])) {
            foreach ($result['items'] as $index => &$item) {
                foreach ($item as $key => &$value) {
                    if ($key == 'is_approved' && $value == null && isset($item['entity_id']) && $item['entity_id'] != null) {
                        $value = 'pending';
                        $this->helperData->setApprovePendingById($item['entity_id']);
                    }
                }
            }
        }

        return $result;
    }
}