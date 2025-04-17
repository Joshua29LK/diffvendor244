<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;

/**
 * Class PostDataProcessor
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email
 */
class PostDataProcessor
{
    /**
     * Fields list with "Use config" checkbox
     */
    const USE_CONFIG_FIELDS_LIST = [
        EmailContentInterface::SENDER_NAME,
        EmailContentInterface::SENDER_EMAIL,
        EmailContentInterface::HEADER_TEMPLATE,
        EmailContentInterface::FOOTER_TEMPLATE
    ];

    /**
     * Prepare entity data for save
     *
     * @param array $data
     * @return array
     */
    public function prepareEntityData($data)
    {
        $preparedData = [];

        foreach ($data as $key => $param) {
            $preparedData[$key] = $param;
        }

        $preparedData = $this->prepareUseConfigFields($preparedData);

        if (isset($preparedData[EmailInterface::AB_TESTING_MODE]) && $preparedData[EmailInterface::AB_TESTING_MODE]) {
            $preparedData[EmailInterface::PRIMARY_EMAIL_CONTENT] = 0;
        }

        return $preparedData;
    }

    /**
     * @param array $data
     * @return array
     */
    private function prepareUseConfigFields($data)
    {
        if (isset($data['content'])) {
            foreach ($data['content'] as $key => &$value) {
                if (isset($value['use_config'])) {
                    $useConfig = $value['use_config'];
                    foreach (self::USE_CONFIG_FIELDS_LIST as $paramName) {
                        if (isset($useConfig[$paramName])
                            && $useConfig[$paramName]) {
                            $value[$paramName] = '';
                        }
                    }
                    unset($value['use_config']);
                }
            }
        }

        return $data;
    }
}
