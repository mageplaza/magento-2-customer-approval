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

namespace Mageplaza\CustomerApproval\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class AttributeOptions
 * @package Mageplaza\CustomerApproval\Model\Config\Source
 */
class AttributeOptions extends AbstractSource
{
    const PENDING    = 'pending';
    const APPROVED   = 'approve';
    const NOTAPPROVE = 'notapprove';

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['label' => __('Pending'), 'value' => self::PENDING],
            ['label' => __('Approved'), 'value' => self::APPROVED],
            ['label' => __('Not Approve'), 'value' => self::NOTAPPROVE],
        ];


        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     *
     * @return string|bool
     */
    public function getOptionValue($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }

        return false;
    }
}