<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleServiceContractReplacement\Test\Unit\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SampleServiceContractReplacement\Model\CartRepository;
use Magento\SampleServiceContractReplacement\Model\QuoteRepository;
use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class CartRepositoryTest
 */
class CartRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CartRepository
     */
    private $cartRepository;

    /**
     * @var QuoteRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

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
        $this->quoteMock = $this->getMockForAbstractClass(CartInterface::class);
        $this->quoteRepositoryMock = $this->getMockBuilder(QuoteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->messageMock = $this->getMockForAbstractClass(MessageInterface::class);

        $this->cartRepository = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            CartRepository::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'cache' => $this->cacheMock,
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 0
     */
    public function testGetNonExistingId()
    {
        $this->quoteRepositoryMock->expects($this->any())
            ->method('get')
            ->willThrowException(new NoSuchEntityException(__('No such entity with cartId = 0')));

        $this->cartRepository->get(0);
    }

    public function testGet()
    {
        $this->cacheMock
            ->expects($this->once())
            ->method('load')
            ->willReturn(serialize($this->messageMock));

        $giftMsg = $this->cartRepository->get(1);

        $this->assertEquals($this->messageMock, $giftMsg);
        $this->assertInstanceOf(MessageInterface::class, $giftMsg);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 1
     */
    public function testSaveWithNoSuchEntityException()
    {
        $this->quoteRepositoryMock->expects($this->any())
            ->method('get')
            ->willThrowException(new NoSuchEntityException(__('No such entity with cartId = 1')));

        $this->cartRepository->save(1, $this->messageMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Gift Messages is not applicable for empty cart
     */
    public function testSaveWithInputException()
    {
        $this->quoteRepositoryMock->expects($this->any())
            ->method('get')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(0);

        $this->cartRepository->save(1, $this->messageMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage Gift Messages is not applicable for virtual products
     */
    public function testSaveWithInvalidTransitionException()
    {
        $this->quoteRepositoryMock->expects($this->any())
            ->method('get')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(1);
        $this->quoteMock->expects($this->once())->method('getIsVirtual')->willReturn(true);

        $this->cartRepository->save(1, $this->messageMock);
    }

    public function testSave()
    {
        $this->quoteRepositoryMock->expects($this->any())
            ->method('get')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(1);
        $this->quoteMock->expects($this->once())->method('getIsVirtual')->willReturn(false);

        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->quoteMock->expects($this->once())->method('getCustomer')->willReturn($customerMock);

        $this->messageMock->expects($this->any())->method('setCustomerId');
        $this->messageMock->expects($this->any())->method('setGiftMessageId');

        $this->cacheMock
            ->expects($this->once())
            ->method('save');

        $this->assertTrue($this->cartRepository->save(1, $this->messageMock));
    }
}


