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

namespace Mageplaza\CustomerApproval\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class TypeAction
 * @package Mageplaza\CustomerApproval\Model\Config\Source
 */
class TypeAction implements ArrayInterface
{
    const COMMAND = 'command';
    const API = 'api';
    const OTHER = 'other';
    const EDITCUSTOMER = 'edit_customer';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function toArray()
    {
        return [
            self::COMMAND => __('Command'),
            self::API => __('Api'),
            self::OTHER => __('Other'),
            self::EDITCUSTOMER => __('Edit Customer')
        ];
    }
}
