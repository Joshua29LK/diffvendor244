<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email\PostDataProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Zend\EventManager\EventManagerAwareInterface;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Email\ResponseDataProcessor
 */
class PostDataProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PostDataProcessor
     */
    private $model;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->model = $objectManager->getObject(PostDataProcessor::class, []);
    }

    /**
     * Test prepareEntityData method
     *
     * @param array $postData
     * @param array $result
     * @dataProvider prepareEntityDataDataProvider
     */
    public function testPrepareEntityData($postData, $result)
    {
        $this->assertEquals($result, $this->model->prepareEntityData($postData));
    }

    /**
     * @return array
     */
    public function prepareEntityDataDataProvider()
    {
        $eventId = 1;
        $emailId = 10;
        return [
            [
                'postData' => [
                    EmailInterface::ID => $emailId,
                    EmailInterface::EVENT_ID => $eventId,
                    EmailInterface::NAME => 'Test email',
                    EmailInterface::AB_TESTING_MODE => 1,
                    EmailInterface::PRIMARY_EMAIL_CONTENT => EmailInterface::CONTENT_VERSION_A,
                    EmailInterface::CONTENT => [
                        [
                            EmailContentInterface::SENDER_NAME => 'Sender A',
                            EmailContentInterface::SENDER_EMAIL => 'sender_a@example.com',
                            EmailContentInterface::HEADER_TEMPLATE => 'design_email_header_template',
                            EmailContentInterface::FOOTER_TEMPLATE => 'design_email_footer_template',
                            'use_config' => [
                                EmailContentInterface::SENDER_NAME => 0,
                                EmailContentInterface::SENDER_EMAIL => 0,
                                EmailContentInterface::HEADER_TEMPLATE => 0,
                                EmailContentInterface::FOOTER_TEMPLATE => 0,
                            ]
                        ],
                    ],
                ],
                'result' => [
                    EmailInterface::ID => $emailId,
                    EmailInterface::EVENT_ID => $eventId,
                    EmailInterface::NAME => 'Test email',
                    EmailInterface::AB_TESTING_MODE => 1,
                    EmailInterface::PRIMARY_EMAIL_CONTENT => 0,
                    EmailInterface::CONTENT => [
                        [
                            EmailContentInterface::SENDER_NAME => 'Sender A',
                            EmailContentInterface::SENDER_EMAIL => 'sender_a@example.com',
                            EmailContentInterface::HEADER_TEMPLATE => 'design_email_header_template',
                            EmailContentInterface::FOOTER_TEMPLATE => 'design_email_footer_template',
                        ],
                    ],
                ],
            ],
            [
                'postData' => [
                    EmailInterface::ID => $emailId,
                    EmailInterface::EVENT_ID => $eventId,
                    EmailInterface::NAME => 'Test email',
                    EmailInterface::AB_TESTING_MODE => 1,
                    EmailInterface::PRIMARY_EMAIL_CONTENT => EmailInterface::CONTENT_VERSION_A,
                    EmailInterface::CONTENT => [
                        [
                            EmailContentInterface::SENDER_NAME => 'Sender A',
                            EmailContentInterface::SENDER_EMAIL => 'sender_a@example.com',
                            EmailContentInterface::HEADER_TEMPLATE => 'design_email_header_template',
                            EmailContentInterface::FOOTER_TEMPLATE => 'design_email_footer_template',
                            'use_config' => [
                                EmailContentInterface::SENDER_NAME => 1,
                                EmailContentInterface::SENDER_EMAIL => 1,
                                EmailContentInterface::HEADER_TEMPLATE => 1,
                                EmailContentInterface::FOOTER_TEMPLATE => 1,
                            ]
                        ],
                    ],
                ],
                'result' => [
                    EmailInterface::ID => $emailId,
                    EmailInterface::EVENT_ID => $eventId,
                    EmailInterface::NAME => 'Test email',
                    EmailInterface::AB_TESTING_MODE => 1,
                    EmailInterface::PRIMARY_EMAIL_CONTENT => 0,
                    EmailInterface::CONTENT => [
                        [
                            EmailContentInterface::SENDER_NAME => '',
                            EmailContentInterface::SENDER_EMAIL => '',
                            EmailContentInterface::HEADER_TEMPLATE => '',
                            EmailContentInterface::FOOTER_TEMPLATE => '',
                        ],
                    ],
                ],
            ],
        ];
    }
}
