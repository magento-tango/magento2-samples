<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleServiceContractNew\Test\Unit\Model;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria;
use Magento\SampleServiceContractNew\Api\Data\FeedInterface;
use Magento\SampleServiceContractNew\Api\Data\FeedSearchResultInterface;
use Magento\SampleServiceContractNew\Api\Data\FeedSearchResultInterfaceFactory;
use Magento\SampleServiceContractNew\Model\FeedManager;
use Magento\SampleServiceContractNew\Model\FeedRepository;

/**
 * Class FeedRepositoryTest
 */
class FeedRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  FeedManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $feedManager;

    /**
     * @var FeedSearchResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultFactory;

    /**
     * @var FeedRepository
     */
    private $feedRepository;

    protected function setUp()
    {
        $this->feedManager = $this->getMockBuilder(FeedManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultFactory = $this->getMockBuilder(FeedSearchResultInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->feedRepository = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            FeedRepository::class,
            [
                'feedManager' => $this->feedManager,
                'searchResultFactory' => $this->searchResultFactory
            ]
        );
    }

    /**
     * @param array $feeds
     * @param array $filterGroups
     * @param array $expectedFilteredFeeds
     * @dataProvider getListDataProvider
     */
    public function testGetList(array $feeds, array $filterGroups, array $expectedFilteredFeeds)
    {
        $this->feedManager->expects($this->once())
            ->method('getFeeds')
            ->willReturn($feeds);
        $searchResult = $this->getMockBuilder(FeedSearchResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchResult->expects($this->once())
            ->method('setItems')
            ->with($expectedFilteredFeeds)
            ->willReturnSelf();
        $this->searchResultFactory->expects($this->once())
            ->method('create')
            ->willReturn($searchResult);

        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteria->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn($filterGroups);

        $this->assertEquals($searchResult, $this->feedRepository->getList($searchCriteria));
    }

    /**
     * @return array
     */
    public function getListDataProvider()
    {
        $feeds = [
            'empty' => $this->createFeed(null, null, null),
            'test1' => $this->createFeed('volutpat', 'Lorem ipsum dolor sit amet', 'Nam volutpat tincidunt leo quis'),
            'test2' => $this->createFeed('tincidunt', 'Nam volutpat tincidunt leo quis', 'Lorem ipsum dolor sit amet'),
            'test3' => $this->createFeed('quis', 'Nam volutpat tincidunt leo quis', 'Lorem ipsum dolor sit amet'),
            'test4' => $this->createFeed('quis', 'Nam volutpat tincidunt leo quis', 'Nam volutpat tincidunt leo quis'),
        ];

        return [
            'noFilters' => [
                'feeds' => $feeds,
                'filterGroups' => [],
                'expectedFeeds' => array_values($feeds),
            ],
            'filterById' => [
                'feeds' => $feeds,
                'filterGroups' => [$this->createFilterGroup([$this->createFilter('id', 'volutpat')])],
                'expectedFeed' => [$feeds['test1']],
            ],
            'filterByTitle' => [
                'feeds' => $feeds,
                'filterGroups' => [$this->createFilterGroup([$this->createFilter('title', 'volutpat')])],
                'expectedFeed' => [$feeds['test2'], $feeds['test3'], $feeds['test4']],
            ],
            'filterByDescription' => [
                'feeds' => $feeds,
                'filterGroups' => [$this->createFilterGroup([$this->createFilter('description', 'Nam')])],
                'expectedFeed' => [$feeds['test1'], $feeds['test4']],
            ],
            'filterByUnknownField' => [
                'feeds' => $feeds,
                'filterGroups' => [$this->createFilterGroup([$this->createFilter('comment', 'Nam')])],
                'expectedFeed' => [],
            ],
            'filterByTitleAndDescription' => [
                'feeds' => $feeds,
                'filterGroups' => [
                    $this->createFilterGroup([
                        $this->createFilter('title', 'volutpat'),
                        $this->createFilter('description', 'tincidunt')
                    ])
                ],
                'expectedFeed' => [$feeds['test4']],
            ]
        ];
    }

    public function testGetById()
    {
        $id = 'feedIdentifier';

        $this->feedManager->expects($this->once())
            ->method('getFeed')
            ->with($id)
            ->willReturn('customFeed');

        $this->assertEquals('customFeed', $this->feedRepository->getById($id));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Feed feedIdentifier not found
     */
    public function testGetByIdNotFoundException()
    {
        $id = 'feedIdentifier';

        $this->feedManager->expects($this->once())
            ->method('getFeed')
            ->with($id)
            ->willReturn(null);

        $this->feedRepository->getById($id);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return Filter
     */
    private function createFilter($field, $value)
    {
        /** @var Filter $filter */
        $filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->any())
            ->method('getField')
            ->willReturn($field);
        $filter->expects($this->any())
            ->method('getValue')
            ->willReturn($value);

        return $filter;
    }

    /**
     * @param array $filters
     * @return FilterGroup
     */
    private function createFilterGroup(array $filters)
    {
        /** @var FilterGroup $filterGroup */
        $filterGroup = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterGroup->expects($this->atLeastOnce())
            ->method('getFilters')
            ->willReturn($filters);

        return $filterGroup;
    }

    /**
     * @param string $id
     * @param string $title
     * @param string $description
     * @return FeedInterface
     */
    private function createFeed($id = 'fieldId', $title = 'fieldTitle', $description = 'fieldDescription')
    {
        /** @var FeedInterface $feed */
        $feed = $this->getMockBuilder(FeedInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $feed->expects($this->any())
            ->method('getId')
            ->willReturn($id);
        $feed->expects($this->any())
            ->method('getTitle')
            ->willReturn($title);
        $feed->expects($this->any())
            ->method('getDescription')
            ->willReturn($description);

        return $feed;
    }
}
