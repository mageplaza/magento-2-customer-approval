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

define([
    'Magento_Ui/js/grid/columns/select'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },
        getLabel: function (record) {
            var label = this._super(record);
            if (String(record.is_approved) === 'pending') {
                label = '<span class="grid-severity-notice" style="background:#fffbbb; color:#37af0c"><span>Pending</span></span>';
            } else if (String(record.is_approved) === 'notapproved') {
                label = '<span  class="grid-severity-minor"><span>Not Approved</span></span>';
            } else {
                label = '<span class="grid-severity-notice"><span>Approved</span></span>';
            }
            return label;
        }
    });
});