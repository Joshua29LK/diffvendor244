<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model;

use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Serialize\Serializer\Serialize as PhpSerialize;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\Followupemail2\Model\Serializer
 */
class SerializerTest extends TestCase
{
    /**
     * @var Serializer
     */
    private $serializerModel;

    /**
     * @var PhpSerialize|\PHPUnit_Framework_MockObject_MockObject
     */
    private $phpSerializeMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
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

        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->phpSerializeMock = $this->createMock(PhpSerialize::class);

        $this->serializerModel = $objectManager->getObject(
            Serializer::class,
            [
                'phpSerialize' => $this->phpSerializeMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * Test serialize method
     */
    public function testSerialize()
    {
        $originalData = 'testSerialize';
        $serializedData = json_encode($originalData);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($originalData)
            ->willReturn($serializedData);

        $this->assertEquals($serializedData, $this->serializerModel->serialize($originalData));
    }

    /**
     * Test unserialize method
     */
    public function testUnserialize()
    {
        $originalData = json_encode('testUnserialize');
        $unserializedData = null;
        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->with($originalData)
            ->willReturn($unserializedData);

        $this->assertEquals($unserializedData, $this->serializerModel->unserialize($originalData));
    }

    /**
     * Test unserialize by php unserialize
     */
    public function testUnserializePhp()
    {
        $originalData = 's:15:"testUnserialize";';
        $unserializedData = 'testUnserialize';
        $this->phpSerializeMock->expects($this->once())
            ->method('unserialize')
            ->with($originalData)
            ->willReturn($unserializedData);

        $this->assertEquals($unserializedData, $this->serializerModel->unserialize($originalData));
    }
}
