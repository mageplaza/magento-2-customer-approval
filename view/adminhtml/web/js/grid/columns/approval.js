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

define([
        'Magento_Ui/js/grid/columns/select',
        'mage/translate'
    ], function (Column, $t) {
        'use strict';

        return Column.extend({
            defaults: {
                bodyTmpl: 'ui/grid/cells/html'
            },
            getLabel: function (record) {
                var columnVal = record.is_approved[0];

                if (columnVal === 'pending') {
                    return '<span class="grid-severity-notice" style="background:#fffbbb; color:#f38a5e; border-color: #f38a5e"><span>' + $t('Pending') + '</span></span>';
                } else if (columnVal === 'notapproved') {
                    return  '<span  class="grid-severity-minor"><span>' + $t('Not Approved') + '</span></span>';
                } else if (columnVal === 'approved'){
                    return  '<span class="grid-severity-notice"><span>' + $t('Approved') + '</span></span>';
                }

                return '';
            }
        });
    }
);

