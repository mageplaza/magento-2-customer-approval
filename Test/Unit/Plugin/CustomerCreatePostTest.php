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

namespace Mageplaza\CustomerApproval\Test\Unit\Plugin;


use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\App\ResponseFactory;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Magento\Customer\Controller\Account\CreatePost;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Mageplaza\CustomerApproval\Plugin\CustomerCreatePost;

/**
 * Class CustomerCreatePostTest
 * @package Mageplaza\CustomerApproval\Test\Unit\Plugin
 */
class CustomerCreatePostTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var RedirectInterface
     */
    protected $_redirect;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var PhpCookieManager
     */
    private $_response;

    /**
     * @var Redirect
     */
    private $object;

    /**
     * @Setup development
     */
    protected function setUp()
    {
        $this->messageManager        = $this->getMockBuilder(ManagerInterface::class)->getMock();
        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)->disableOriginalConstructor()->getMock();
        $this->_redirect             = $this->getMockBuilder(RedirectInterface::class)->getMock();
        $this->_customerSession      = $this->getMockBuilder(CustomerSession::class)->disableOriginalConstructor()->getMock();
        $this->_response             = $this->getMockBuilder(ResponseFactory::class)->disableOriginalConstructor()->getMock();
        $this->helperData            = $this->getMockBuilder(HelperData::class)->disableOriginalConstructor()->getMock();

        $this->object = new CustomerCreatePost($this->messageManager, $this->resultRedirectFactory, $this->_redirect,
            $this->_customerSession, $this->_response, $this->helperData);
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
        $url = 'http://example.com/';
        $this->helperData->method('isEnabled')->willReturn(1);
        $this->_customerSession->method('getCustomerId')->with('isForce')->willReturn(1);
        #if customerId return true;
        $this->helperData->method('getCustomerById')->willReturn(1);
        $this->helperData->method('getStoreId')->willReturn(1);
        $this->helperData->method('getEnabledNoticeAdmin')->willReturn(1);
        $this->helperData->method('getEnabledSuccessEmail')->willReturn(1);



        $result = $this->getMockBuilder(ResultInterface::class)->setMethods(['setUrl'])->with($url)
            ->willReturnSelf();
        $this->resultRedirectFactory
            ->expects($this->atLeastOnce())
            ->method('create')
            ->with('redirect')
            ->willReturn($result);

        /** @var \Magento\Customer\Model\Account\Redirect $redirectOj */
        $redirectOj = (new ObjectManager($this))->getObject(LoggedRedirect::class);
        $this->assertEquals($result, $this->object->aroundGetRedirect($redirectOj, $this->mockPluginProceed()));
    }

    /**
     * @param null $returnValue
     *
     * @return \Closure
     */
    protected function mockPluginProceed($returnValue = null)
    {
        return function () use ($returnValue) {
            return $returnValue;
        };
    }


}