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

namespace Mageplaza\CustomerApproval\Api;

/**
 * Interface ListApproveInterface
 *
 * @package Mageplaza\CustomerApproval\Api
 */
interface ListApproveInterface
{
    /**
     * Approve customer
     *
     * @param string $email
     *
     * @return string
     */
    public function approveCustomer($email);

    /**
     * Not Approve customer
     *
     * @param string $email
     *
     * @return string
     */
    public function notApproveCustomer($email);
}
