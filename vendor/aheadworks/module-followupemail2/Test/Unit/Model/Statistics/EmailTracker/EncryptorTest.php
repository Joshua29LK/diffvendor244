<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Statistics\EmailTracker;

use Aheadworks\Followupemail2\Model\Serializer;
use Aheadworks\Followupemail2\Model\Statistics\EmailTracker\Encryptor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Test for \Aheadworks\Followupemail2\Model\Statistics\EmailTracker\Encryptor
 */
class EncryptorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Encryptor
     */
    private $model;

    /**
     * @var EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $encryptorMock;

    /**
     * @var Serializer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->encryptorMock = $this->getMockBuilder(EncryptorInterface::class)
            ->getMockForAbstractClass();

        $this->serializerMock = $this->createMock(Serializer::class);

        $this->model = $objectManager->getObject(
            Encryptor::class,
            [
                'encryptor' => $this->encryptorMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * Test encrypt-decrypt sequence
     */
    public function testEncryptDecrypt()
    {
        $params = [
            'param1' => 1,
            'param2' => 2,
        ];
        $encryptedParams = 'ABCDEFG1234567890';

        $this->encryptorMock->expects($this->once())
            ->method('encrypt')
            ->with(json_encode($params))
            ->willReturn($encryptedParams);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($params)
            ->willReturn(json_encode($params));
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with(json_encode($params))
            ->willReturn($params);
        $this->encryptorMock->expects($this->once())
            ->method('decrypt')
            ->with($encryptedParams)
            ->willReturn(json_encode($params));

        $this->assertEquals($params, $this->model->decrypt($this->model->encrypt($params)));
    }
}
