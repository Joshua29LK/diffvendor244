<?php
namespace Aheadworks\Followupemail2\Model\Statistics\EmailTracker;

use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class Encryptor
 * @package Aheadworks\Followupemail2\Model\Statistics\EmailTracker
 */
class Encryptor
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param EncryptorInterface $encryptor
     * @param Serializer $serializer
     */
    public function __construct(
        EncryptorInterface $encryptor,
        Serializer $serializer
    ) {
        $this->encryptor = $encryptor;
        $this->serializer = $serializer;
    }

    /**
     * Encrypt tracking params
     *
     * @param array $params
     * @return string
     */
    public function encrypt($params)
    {
        return base64_encode($this->encryptor->encrypt($this->serializer->serialize($params)));
    }

    /**
     * Decrypt tracking key
     *
     * @param string $key
     * @return array
     */
    public function decrypt($key)
    {
        $serializedParams = $this->encryptor->decrypt(base64_decode($key));
        return $this->serializer->unserialize($serializedParams);
    }
}
