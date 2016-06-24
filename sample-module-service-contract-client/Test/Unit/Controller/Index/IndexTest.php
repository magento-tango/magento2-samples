<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleServiceContractClient\Test\Unit\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\SampleServiceContractClient\Controller\Index\Index;

/**
 * Class IndexTest
 */
class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @var Index
     */
    private $controller;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            Index::class,
            [
                'context' => $this->contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
            ]
        );
    }

    public function testExecute()
    {
        $page = 'SamplePageObjectHere';
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($page);
        $result = $this->controller->execute();

        $this->assertEquals($page, $result);
    }
}
