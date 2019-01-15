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

use Magento\Framework\Option\ArrayInterface;

/**
 * Class RedirectCmsPage
 * @package Mageplaza\CustomerApproval\Model\Config\Source
 */
class RedirectCmsPage implements ArrayInterface
{
    const NOT_APPROVE_CUSTOMER_PAGE = 'not_approve_cutomer_page';
    const CMS_NOT_FOUNd             = 'cms_not_found';
    const ENABLE_COOKIES            = 'enable_cookies';
    const HOME_PAGE                 = 'home_page';
    const PRIVACY_POLICY            = 'privacy_policy';
    const ABOUT_US                  = 'about_us';
    const CUSTOMER_SERVICE          = 'customer_service';

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
            self::NOT_APPROVE_CUSTOMER_PAGE => __('Not Approve Cutomer Page'),
            self::CMS_NOT_FOUNd             => __('404 Not Found'),
            self::ENABLE_COOKIES            => __('Enable Cookies'),
            self::HOME_PAGE                 => __('Home Page'),
            self::PRIVACY_POLICY            => __('Privacy Policy'),
            self::ABOUT_US                  => __('About Us'),
            self::CUSTOMER_SERVICE          => __('Customer Service')
        ];
    }
}
