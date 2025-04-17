<?php

namespace Aheadworks\Followupemail2\Model;

use Magento\Framework\Serialize\Serializer\Serialize as PhpSerialize;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Serializer
 * @package Aheadworks\Followupemail2\Model
 */
class Serializer
{
    /**
     * Serialized pattern
     */
    const SERIALIZED_PATTERN = '/^((s|i|d|b|a|O|C):|N;)/';

    /**
     * @var PhpSerialize
     */
    private $phpSerialize;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param PhpSerialize $phpSerialize
     * @param SerializerInterface $serializer
     */
    public function __construct(
        PhpSerialize $phpSerialize,
        SerializerInterface $serializer
    ) {
        $this->phpSerialize = $phpSerialize;
        $this->serializer = $serializer;
    }

    /**
     * Unserialize data
     *
     * @param $data
     * @return array|bool|float|int|mixed|string|null
     */
    public function unserialize($data)
    {
        if (empty($data)) {
            return null;
        }

        if ($this->isSerialized($data)) {
            return $this->phpSerialize->unserialize($data);
        }

        if (is_string($data) && !is_array(json_decode($data, true))) {
            return null;
        }

        return $this->serializer->unserialize($data);
    }

    /**
     * Serialize data
     *
     * @param $data
     * @return bool|string
     */
    public function serialize($data)
    {
        return $this->serializer->serialize($data);
    }

    /**
     * Check if value is a serialized string
     *
     * @param string $value
     * @return boolean
     */
    private function isSerialized($value)
    {
        return (bool)preg_match(self::SERIALIZED_PATTERN, $value ?? '');
    }
}
