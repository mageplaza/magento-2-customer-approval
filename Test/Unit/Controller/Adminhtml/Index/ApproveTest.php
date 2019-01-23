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

namespace Mageplaza\CustomerApproval\Test\Unit\Controller\Adminhtml\Index;

use Magento\Customer\Model\Account\Redirect as LoggedRedirect;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\CustomerApproval\Controller\Adminhtml\Index\Approve;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;

/**
 * Class ApproveTest
 * @package Mageplaza\CustomerApproval\Test\Unit\Controller\Adminhtml\Index
 */
class ApproveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_session;

    /**
     * @var HelperData|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperData;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManager;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var DecoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlDecoder;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilder;

    /**
     * @var Redirect
     */
    private $object;

    protected function setUp()
    {
        $this->_session      = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $this->_helperData   = $this->getMockBuilder(HelperData::class)->disableOriginalConstructor()->getMock();
        $this->_storeManager = $this->getMockBuilder(StoreManagerInterface::class)->getMock();
        $this->_request      = $this->getMockBuilder(RequestInterface::class)->getMock();
        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)->disableOriginalConstructor()->getMock();
        $this->_urlDecoder   = $this->getMockBuilder(DecoderInterface::class)->disableOriginalConstructor()->getMock();
        $this->_urlBuilder   = $this->getMockBuilder(UrlInterface::class)->getMock();

        $this->object = new Approve($this->_session, $this->_helperData, $this->_storeManager,
            $this->_request, $this->resultFactory, $this->_urlDecoder, $this->_urlBuilder);
    }

    public function testAdminInstance()
    {
        $this->assertInstanceOf(Approve::class, $this->object);
    }

    public function testExecute()
    {
        $customerId = '9';
        $this->_helperData->method('isEnabled')->willReturn(1);
        $this->_helperData->approvalCustomerById($customerId);
    }

    /**
     * @param mixed $returnValue
     *
     * @return callable
     */
    protected function mockPluginProceed($returnValue = null)
    {
        return function () use ($returnValue) {
            return $returnValue;
        };
    }


}