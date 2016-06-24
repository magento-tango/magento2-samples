<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleServiceContractReplacement\Test\Unit\Model;

use Magento\Quote\Model\QuoteRepository\LoadHandler;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\SampleServiceContractReplacement\Model\QuoteRepository;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;

/**
 * Class QuoteRepositoryTest
 */
class QuoteRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QuoteRepository
     */
    private $model;

    /**
     * @var QuoteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteFactoryMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var CartSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsDataFactory;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteCollectionMock;

    /**
     * @var JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $joinProcessorMock;

    /**
     * @var LoadHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loadHandlerMock;

    protected function setUp()
    {
        $this->quoteFactoryMock = $this->getMockBuilder(QuoteFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'load',
                    'loadByCustomer',
                    'getIsActive',
                    'getId',
                    '__wakeup',
                    'setSharedStoreIds',
                    'save',
                    'delete',
                    'getCustomerId'
                ]
            )->getMock();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultsDataFactory = $this->getMockBuilder(CartSearchResultsInterfaceFactory::class)
            ->setMethods(['create'])
            ->getMock();
        $this->quoteCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->joinProcessorMock = $this->getMockBuilder(JoinProcessorInterface::class)
            ->getMockForAbstractClass();
        $this->loadHandlerMock = $this->getMockBuilder(LoadHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            QuoteRepository::class,
            [
                'quoteFactory' => $this->quoteFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'quoteCollection' => $this->quoteCollectionMock,
                'searchResultsDataFactory' => $this->searchResultsDataFactory,
                'extensionAttributesJoinProcessor' => $this->joinProcessorMock,
                'loadHandler' => $this->loadHandlerMock,
            ]
        );
    }

    public function testGet()
    {
        $cartId = 15;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($cartId);
        $this->quoteMock->expects($this->exactly(2))->method('getIsActive')->willReturn(1);

        $this->assertEquals($this->quoteMock, $this->model->get($cartId));
        $this->assertEquals($this->quoteMock, $this->model->get($cartId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 14
     */
    public function testGetWithExceptionById()
    {
        $cartId = 14;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(false);
        $this->quoteMock->expects($this->never())->method('getIsActive');

        $this->model->get($cartId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 15
     */
    public function testGetWithExceptionByIsActive()
    {
        $cartId = 15;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($cartId);
        $this->quoteMock->expects($this->once())->method('getIsActive')->willReturn(0);

        $this->model->get($cartId);
    }
}
