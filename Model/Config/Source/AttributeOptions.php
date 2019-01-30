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

/**
 * Class AttributeOptions
 * @package Mageplaza\CustomerApproval\Model\Config\Source
 */
class AttributeOptions extends AbstractSource
{
    const PENDING           = 'pending';
    const APPROVED          = 'approved';
    const NOTAPPROVE        = 'notapproved';
    const NOTAPPROVECONVERT = 'not approved';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['label' => __('Pending'), 'value' => self::PENDING],
            ['label' => __('Approved'), 'value' => self::APPROVED],
            ['label' => __('Not Approved'), 'value' => self::NOTAPPROVE],
        ];

        return $this->_options;
    }
}