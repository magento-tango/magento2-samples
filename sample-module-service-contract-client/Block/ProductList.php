<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleServiceContractClient\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\Data\ProductTypeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductTypeListInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class ProductList
 */
class ProductList extends Template
{
    /**
     * @var ProductTypeListInterface
     */
    private $productTypeList;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param ProductTypeListInterface $productTypeList
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductTypeListInterface $productTypeList,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productTypeList = $productTypeList;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return ProductSearchResultsInterface
     */
    public function getProducts()
    {
        return $this->productRepository->getList(
            $this->buildSearchCriteria()
        );
    }

    /**
     * @return ProductTypeInterface[]
     */
    public function getProductTypes()
    {
        return $this->productTypeList->getProductTypes();
    }

    /**
     * @param ProductTypeInterface $productType
     * @return bool
     */
    public function isTypeActive(ProductTypeInterface $productType)
    {
        return $this->getType() === $productType->getName();
    }

    /**
     * @return string
     */
    private function getType()
    {
        return $this->getRequest()->getParam('type');
    }

    /**
     * @return \Magento\Framework\Api\SearchCriteria
     */
    private function buildSearchCriteria()
    {
        if ($this->getType()) {
            return $this->searchCriteriaBuilder->addFilter(ProductInterface::TYPE_ID, $this->getType())->create();
        }

        return $this->searchCriteriaBuilder->create();
    }
}
