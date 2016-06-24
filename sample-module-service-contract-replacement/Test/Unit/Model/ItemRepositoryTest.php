<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleServiceContractReplacement\Test\Unit\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\CacheInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\SampleServiceContractReplacement\Model\ItemRepository;
use Magento\SampleServiceContractReplacement\Model\QuoteRepository;
use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class ItemRepositoryTest
 */
class ItemRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * @var QuoteRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var CartItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItemMock;

    /**
     * @var CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageMock;

    protected function setUp()
    {
        $this->quoteItemMock = $this->getMockForAbstractClass(CartItemInterface::class);

        /** @var \Magento\Quote\Api\CartItemRepositoryInterface $quoteItemRepository */
        $quoteItemRepository = $this->getMockForAbstractClass(CartItemRepositoryInterface::class);
        $quoteItemRepository->expects($this->any())->method('getList')->willReturn([$this->quoteItemMock]);

        $this->quoteMock = $this->getMockForAbstractClass(CartInterface::class);
        $this->quoteRepositoryMock = $this->getMockBuilder(QuoteRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getList'])
            ->getMock();
        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->messageMock = $this->getMockForAbstractClass(MessageInterface::class);

        $this->itemRepository = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            ItemRepository::class,
            [
                'quoteItemRepository' => $quoteItemRepository,
                'quoteRepository' => $this->quoteRepositoryMock,
                'cache' => $this->cacheMock
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage  There is no item with provided id in the cart
     */
    public function testGetNonExistingId()
    {
        $this->quoteItemMock->expects($this->once())->method('getItemId')->willReturn(1);

        $this->itemRepository->get(0, 0);
    }

    public function testGet()
    {
        $this->quoteItemMock->expects($this->once())->method('getItemId')->willReturn(1);
        $this->cacheMock->expects($this->once())->method('load')->willReturn(serialize($this->messageMock));

        $this->assertEquals($this->messageMock, $this->itemRepository->get(2, 1));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 0
     */
    public function testSaveWithNoSuchEntityException()
    {
        $this->quoteRepositoryMock->expects($this->any())
            ->method('get')
            ->willThrowException(new NoSuchEntityException(__('No such entity with cartId = 0')));

        $this->itemRepository->save(0, $this->messageMock, 0);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no item with provided id in the cart
     */
    public function testSaveWithNoSuchEntityExceptionItem()
    {
        $this->quoteRepositoryMock->expects($this->any())->method('get')->willReturn($this->quoteMock);
        $this->quoteItemMock->expects($this->once())->method('getItemId')->willReturn(1);

        $this->itemRepository->save(0, $this->messageMock, 0);
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage Gift Messages is not applicable for virtual products
     */
    public function testSaveWithInvalidTransitionException()
    {
        $this->quoteRepositoryMock->expects($this->any())->method('get')->willReturn($this->quoteMock);
        $this->quoteItemMock->expects($this->once())->method('getItemId')->willReturn(1);
        $this->quoteItemMock
            ->expects($this->once())
            ->method('getProductType')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL);

        $this->itemRepository->save(2, $this->messageMock, 1);
    }

    public function testSave()
    {
        $this->quoteRepositoryMock->expects($this->any())->method('get')->willReturn($this->quoteMock);
        $this->quoteItemMock->expects($this->once())->method('getItemId')->willReturn(1);
        $this->quoteItemMock
            ->expects($this->once())
            ->method('getProductType')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);

        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);

        $this->quoteMock->expects($this->once())->method('getCustomer')->willReturn($customerMock);
        $this->messageMock->expects($this->any())->method('setCustomerId');
        $this->messageMock->expects($this->any())->method('setGiftMessageId');
        $this->cacheMock->expects($this->once())->method('save');

        $this->assertTrue($this->itemRepository->save(2, $this->messageMock, 1));
    }
}
