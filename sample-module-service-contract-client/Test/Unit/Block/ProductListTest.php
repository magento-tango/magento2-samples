<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleServiceContractClient\Test\Unit\Block;

use Magento\Catalog\Api\Data\ProductTypeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductTypeListInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\SampleServiceContractClient\Block\ProductList;

/**
 * Class ProductListTest
 */
class ProductListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var ProductTypeListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productTypeListMock;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var ProductList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $block;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRequest'])
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->productTypeListMock = $this->getMockBuilder(ProductTypeListInterface::class)
            ->getMockForAbstractClass();
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            ProductList::class,
            [
                'context' => $this->contextMock,
                'productTypeList' => $this->productTypeListMock,
                'productRepository' => $this->productRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
            ]
        );
    }

    public function testGetProductTypes()
    {
        $productTypeOne = $this->createProductType('ProductType1Name', 'ProductType1Label');
        $productTypeTwo = $this->createProductType('ProductType2Name', 'ProductType2Label');

        $this->productTypeListMock->expects($this->once())
            ->method('getProductTypes')
            ->willReturn([$productTypeOne, $productTypeTwo]);
        $expectedResult = [
            $productTypeOne,
            $productTypeTwo
        ];

        $this->assertEquals($expectedResult, $this->block->getProductTypes());
    }

    /**
     * @param $requestedType
     * @param $productType
     * @param $expectedValue
     * @dataProvider isTypeActiveDataProvider
     */
    public function testIsTypeActive($requestedType, $productType, $expectedValue)
    {
        $this->requestMock->expects($this->exactly(1))
            ->method('getParam')
            ->with($this->equalTo('type'))
            ->willReturn($requestedType);

        $this->assertEquals($expectedValue, $this->block->isTypeActive($productType));
    }

    /**
     * @return array
     */
    public function isTypeActiveDataProvider()
    {
        return [
            'activeType' => [
                'requestedType' => 'FilteredProductType',
                'productType' => $this->createProductType('FilteredProductType', 'FilteredProductTypeLabel'),
                'expectedValue' => true,
            ],
            'notActiveType' => [
                'requestedType' => 'FilteredProductType',
                'productType' => $this->createProductType('ExampleProductType', 'FilteredProductTypeLabel'),
                'expectedValue' => false,
            ]
        ];
    }

    public function testGetProductsWithoutFilter()
    {
        $products = ['Product1', 'Product2'];
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $this->productRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($products);

        $this->assertEquals($products, $this->block->getProducts());
    }

    public function testGetProductsWithFilter()
    {
        $products = ['Product1', 'Product2'];
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMockForAbstractClass();
        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->with($this->equalTo('type'))
            ->willReturn('FilterProductType');
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $this->productRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($products);

        $this->assertEquals($products, $this->block->getProducts());
    }

    /**
     * @param string $name
     * @param string $label
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createProductType($name, $label)
    {
        $productType = $this->getMockBuilder(ProductTypeInterface::class)
            ->setMethods(['getName', 'getLabel'])
            ->getMockForAbstractClass();
        $productType->expects($this->any())
            ->method('getName')
            ->willReturn($name);
        $productType->expects($this->any())
            ->method('getLabel')
            ->willReturn($label);

        return $productType;
    }
}
