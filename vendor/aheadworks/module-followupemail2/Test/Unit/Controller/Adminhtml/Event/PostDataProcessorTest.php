<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event;

use Aheadworks\Followupemail2\Controller\Adminhtml\Event\PostDataProcessor;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Event\CartConditionConverter;
use Aheadworks\Followupemail2\Model\Event\ProductConditionConverter;
use Aheadworks\Followupemail2\Model\Event\LifetimeConditionConverter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\PostDataProcessor
 */
class PostDataProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PostDataProcessor
     */
    private $model;

    /**
     * @var CartConditionConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartConditionConverterMock;

    /**
     * @var ProductConditionConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productConditionConverterMock;

    /**
     * @var LifetimeConditionConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $lifetimeConditionConverterMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->cartConditionConverterMock = $this->getMockBuilder(CartConditionConverter::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productConditionConverterMock = $this->getMockBuilder(ProductConditionConverter::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->lifetimeConditionConverterMock = $this->getMockBuilder(LifetimeConditionConverter::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            PostDataProcessor::class,
            [
                'cartConditionConverter' => $this->cartConditionConverterMock,
                'productConditionConverter' => $this->productConditionConverterMock,
                'lifetimeConditionConverter' => $this->lifetimeConditionConverterMock
            ]
        );
    }

    /**
     * Test prepareEntityData method
     */
    public function testPrepareEntityData()
    {
        $campaignId = 1;
        $eventId = 2;
        $eventType = EventInterface::TYPE_ABANDONED_CART;
        $eventName = "Event name";
        $cartConditionsPrepared = [];
        $productConditionsPrepared = [];
        $lifetimeConditionsSerialized = '';
        $postData = [
            'id' => $eventId,
            'campaign_id' => $campaignId,
            'event_type' => $eventType,
            'name' => $eventName,
            'newsletter_only' => '',
            'failed_emails_mode' => "1",
            'store_ids' => ["0", "1", "2"],
            'product_type_ids' => ["all"],
            'cart_conditions' => "",
            'product_conditions' => "",
            'lifetime_conditions' => "gt",
            'customer_groups' => ["all"],
            'order_statuses' => ["all"],
            'status' => "0",
            'lifetime_value' => "500",
            'lifetime_from' => "",
            'lifetime_to' => "",
            'duplicate_id' => false,
            'bcc_emails' => "",
            'rule' => [
                'conditions' => [
                    '1' => [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => ''],
                    '1--1' => [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'attribute' => 'base_subtotal',
                        'operator' => '>',
                        'value' => '50'
                    ],
                    '2' => [
                        'type' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => ''],
                    '2--1' => [
                        'type' => \Magento\CatalogRule\Model\Rule\Condition\Product::class,
                        'attribute' => 'color',
                        'operator' => '==',
                        'value' => '50'
                    ]
                ]
            ]
        ];

        $result = [
            'id' => $eventId,
            'campaign_id' => $campaignId,
            'event_type' => $eventType,
            'name' => $eventName,
            'newsletter_only' => '',
            'failed_emails_mode' => '1',
            'store_ids' => ["0"],
            'product_type_ids' => ["all"],
            'cart_conditions' => $cartConditionsPrepared,
            'customer_groups' => ["all"],
            'order_statuses' => ["all"],
            'status' => '0',
            'duplicate_id' => false,
            'bcc_emails' => '',
            'product_conditions' => $productConditionsPrepared,
            'lifetime_conditions' => $lifetimeConditionsSerialized,
        ];

        $this->cartConditionConverterMock->expects($this->once())
            ->method('getConditionsPrepared')
            ->with($postData['rule'])
            ->willReturn($cartConditionsPrepared);
        $this->productConditionConverterMock->expects($this->once())
            ->method('getConditionsPrepared')
            ->with($postData['rule'])
            ->willReturn($productConditionsPrepared);
        $this->lifetimeConditionConverterMock->expects($this->once())
            ->method('getConditionsSerialized')
            ->willReturn($lifetimeConditionsSerialized);

        $this->assertEquals($result, $this->model->prepareEntityData($postData));
    }

    /**
     * Test prepareLifetimeConditions method
     *
     * @param array $input
     * @param array $output
     * @dataProvider prepareLifetimeConditionsDataProvider
     * @throws \ReflectionException
     */
    public function testPrepareLifetimeConditions($input, $output)
    {
        $lifetimeConditionsSerialized = '{serialized_conditions}';
        $this->lifetimeConditionConverterMock->expects($this->any())
            ->method('getConditionsSerialized')
            ->willReturn($lifetimeConditionsSerialized);

        $this->assertEquals(
            $output,
            $this->invokeMethod($this->model, 'prepareLifetimeConditions', [$input])
        );
    }

    /**
     * @return array
     */
    public function prepareLifetimeConditionsDataProvider()
    {

        return [
            [
                [],
                [EventInterface::LIFETIME_CONDITIONS => '']
            ],
            [
                [
                    'lifetime_conditions' => 'gt',
                    'lifetime_value' => '20',
                    'lifetime_from' => '',
                    'lifetime_to' => ''
                ],
                [EventInterface::LIFETIME_CONDITIONS => '{serialized_conditions}']
            ],
            [
                [
                    'lifetime_conditions' => 'range',
                    'lifetime_value' => '',
                    'lifetime_from' => '10',
                    'lifetime_to' => '50'
                ],
                [EventInterface::LIFETIME_CONDITIONS => '{serialized_conditions}']
            ],
        ];
    }

    /**
     * Test prepareCartProductConditions method
     *
     * @param array $input
     * @param array $output
     * @dataProvider prepareCartProductConditionsDataProvider
     * @throws \ReflectionException
     */
    public function testPrepareCartProductConditions($input, $output)
    {
        $cartConditionsPrepared = ['cart_conditions'];
        $productConditionsPrepared = ['product_conditions'];
        if (isset($input['rule'])) {
            $this->cartConditionConverterMock->expects($this->once())
                ->method('getConditionsPrepared')
                ->with($input['rule'])
                ->willReturn($cartConditionsPrepared);
            $this->productConditionConverterMock->expects($this->once())
                ->method('getConditionsPrepared')
                ->with($input['rule'])
                ->willReturn($productConditionsPrepared);
        }

        $this->assertEquals(
            $output,
            $this->invokeMethod($this->model, 'prepareCartProductConditions', [$input])
        );
    }

    /**
     * @return array
     */
    public function prepareCartProductConditionsDataProvider()
    {

        return [
            [
                [],
                [EventInterface::CART_CONDITIONS => '', EventInterface::PRODUCT_CONDITIONS => ''],
            ],
            [
                [EventInterface::CART_CONDITIONS => [], EventInterface::PRODUCT_CONDITIONS => []],
                [EventInterface::CART_CONDITIONS => [], EventInterface::PRODUCT_CONDITIONS => []],
            ],
            [
                [
                    EventInterface::CART_CONDITIONS => '',
                    EventInterface::PRODUCT_CONDITIONS => '',
                    'rule' => ['conditions' => []]
                ],
                [
                    EventInterface::CART_CONDITIONS => ['cart_conditions'],
                    EventInterface::PRODUCT_CONDITIONS => ['product_conditions']
                ],
            ],
        ];
    }

    /**
     * Test prepareStoreIds method
     *
     * @param array $input
     * @param array $output
     * @dataProvider prepareStoreIdsDataProvider
     * @throws \ReflectionException
     */
    public function testPrepareStoreIds($input, $output)
    {
        $this->assertEquals($output, $this->invokeMethod($this->model, 'prepareStoreIds', [$input]));
    }

    /**
     * @return array
     */
    public function prepareStoreIdsDataProvider()
    {
        return [
            [['store_ids' => [0, 1, 2]], ['store_ids' => [0]]],
            [['store_ids' => [1, 2]], ['store_ids' => [1, 2]]],
            [[], []],
        ];
    }

    /**
     * Test prepareListsWithAll method
     *
     * @param array $input
     * @param array $output
     * @dataProvider prepareListsWithAllDataProvider
     * @throws \ReflectionException
     */
    public function testPrepareListsWithAll($input, $output)
    {
        $this->assertEquals(
            $output,
            $this->invokeMethod($this->model, 'prepareListsWithAll', [$input])
        );
    }

    /**
     * @return array
     */
    public function prepareListsWithAllDataProvider()
    {
        return [
            [
                ['customer_groups' => ['all', '1', '2'], 'product_type_ids' => ['all', '1', '2']],
                ['customer_groups' => ['all'], 'product_type_ids' => ['all']]
            ],
            [
                ['customer_groups' => ['1', '2'], 'product_type_ids' => ['1', '2']],
                ['customer_groups' => ['1', '2'], 'product_type_ids' => ['1', '2']]
            ],
            [[], []],
        ];
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object
     * @param string $methodName
     * @param array $parameters
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function invokeMethod(&$object, $methodName, $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
