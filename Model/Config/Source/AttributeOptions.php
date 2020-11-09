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

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class AttributeOptions
 * @package Mageplaza\CustomerApproval\Model\Config\Source
 */
class AttributeOptions extends AbstractSource
{
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const NOTAPPROVE = 'notapproved';
    const NEW_STATUS = 'new';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $options = [];

        foreach ($this->toArray() as $key => $label) {
            $options[] = [
                'value' => $key,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::PENDING => __('Pending'),
            self::APPROVED => __('Approved'),
            self::NOTAPPROVE => __('Not Approved'),
        ];
    }
}
