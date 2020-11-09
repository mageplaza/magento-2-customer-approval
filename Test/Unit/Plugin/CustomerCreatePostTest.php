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

namespace Mageplaza\CustomerApproval\Test\Unit\Plugin;

use Closure;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\Account\CreatePost;
use Magento\Customer\Model\ResourceModel\Customer\AbstractCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CusCollectFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Mageplaza\CustomerApproval\Plugin\CustomerCreatePost;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class CustomerCreatePostTest
 *
 * @package Mageplaza\CustomerApproval\Test\Unit\Plugin
 */
class CustomerCreatePostTest extends TestCase
{
    /**
     * @var HelperData|PHPUnit_Framework_MockObject_MockObject
     */
    private $helperData;

    /**
     * @var ManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var RedirectFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var RedirectInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $_redirect;

    /**
     * @var CustomerSession|PHPUnit_Framework_MockObject_MockObject
     */
    private $_customerSession;

    /**
     * @var ResponseFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $_response;

    /**
     * @var CustomerCreatePost
     */
    private $object;

    /**
     * @var RedirectInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cusCollectFactory;

    /**
     * @Setup development
     */
    protected function setUp()
    {
        $this->helperData = $this->getMockBuilder(HelperData::class)
            ->disableOriginalConstructor()->getMock();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)->getMock();
        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->_redirect = $this->getMockBuilder(RedirectInterface::class)->getMock();
        $this->_customerSession = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()->getMock();
        $this->_response = $this->getMockBuilder(ResponseFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->_cusCollectFactory = $this->getMockBuilder(CusCollectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $this->object = new CustomerCreatePost(
            $this->helperData,
            $this->messageManager,
            $this->resultRedirectFactory,
            $this->_redirect,
            $this->_customerSession,
            $this->_response,
            $this->_cusCollectFactory
        );
    }

    /**
     * @Test Admin Instance
     */
    public function testAdminInstance()
    {
        $this->assertInstanceOf(CustomerCreatePost::class, $this->object);
    }

    /**
     * @Test after create post
     */
    public function testAfterExecute()
    {
        $emailPost = 'example@gmail.com';
        /**
         * @var CreatePost|PHPUnit_Framework_MockObject_MockObject $redirectOj
         */
        $redirectOj = $this->getMockBuilder(CreatePost::class)->setMethods(['getRequest'])->disableOriginalConstructor()->getMock();
        $request = $this->getMockBuilder(RequestInterface::class)->getMock();
        $redirectOj->method('getRequest')->willReturn($request);
        $request->expects($this->once())->method('getParam')->with('email')->willReturn($emailPost);

        $customerFilter = $this->getMockBuilder(AbstractCollection::class)->setMethods([
            'addFieldToFilter',
            'getFirstItem',
            'getId'
        ])->disableOriginalConstructor()->getMock();
        $this->_cusCollectFactory->method('create')->willReturn($customerFilter);

        $customerFilter->expects($this->once())->method('addFieldToFilter')->with(
            'email',
            $emailPost
        )->willReturnSelf();
        $customerFilter->expects($this->once())->method('getFirstItem')->willReturn($customerFilter);
        $customerId = 5;
        $customerFilter->method('getId')->willReturn($customerId);
        $this->helperData->method('isEnabled')->willReturn(1);
        $this->_customerSession->method('isLoggedIn')->willReturn(1);
        $this->_customerSession->method('getCustomerId')->willReturn($customerId);

        $customer = $this->getMockBuilder(CustomerInterface::class)->getMock();
        $this->helperData->expects($this->once())->method('getCustomerById')->with($customerId)->willReturn($customer);
        $this->helperData->expects($this->once())->method('getAutoApproveConfig')->willReturn(1);
        $this->helperData->expects($this->once())->method('approvalCustomerById')->with($customerId);
        $this->helperData->expects($this->once())->method('emailNotifyAdmin')->with($customer);

        /**
         * @var CreatePost|PHPUnit_Framework_MockObject_MockBuilder $redirectOj
         */
        $this->object->afterExecute($redirectOj, $this->mockPluginProceed());
    }

    /**
     * @param null $returnValue
     *
     * @return Closure
     */
    protected function mockPluginProceed($returnValue = null)
    {
        return function () use ($returnValue) {
            return $returnValue;
        };
    }
}
