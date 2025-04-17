<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Block\Adminhtml\Edit\Tab\History\Renderer;

class Details extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Wyomind\Framework\Helper\History
     */
    protected $historyHelper;

    /**
     * Class constructor
     * @param \Magento\Backend\Block\Context $context
     * @param \Wyomind\Framework\Helper\History $historyHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Wyomind\Framework\Helper\History $historyHelper,
        array $data = []
    ) {
    
        $this->historyHelper = $historyHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $updates = ' - ';
        $unserialize = "unserialize";
        try {
            $details = $unserialize($row->getDetails());
        } catch (\Exception $e) {
            $details = [];
        }
        $nbUpdates = sizeof($details);

        if ($nbUpdates > 0) {
            $updates = $nbUpdates . ' ' . __('update(s)')
                . '&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="history-more-info active">' . __('More details') . '</a>'
                . '<a href="#" class="history-less-info">' . __('Less details') . '</a><br/><br/>';

            $updates .= '<div id="history-modal-popup-' . $row->getId() . '"></div>';

            $updates .= '<table class="history-details"><thead><tr>'
                . '<th>' . __('Field') . '</th>'
                . '<th>' . __('Original') . '</th>'
                . '<th>' . __('Current') . '</th>'
                . '</tr></thead><tbody>';

            foreach ($details as $field => $detail) {
                $original = htmlentities((string)$detail['original']);
                $current = htmlentities((string)$detail['current']);

                if (strlen((string)$original) >= 40) {
                    $original = substr((string)$original, 0, 30) . ' [...]';
                }

                if (strlen((string)$current) >= 40) {
                    $current = substr((string)$current, 0, 30) . ' [...]';
                }

                $updates .= '<tr class="history-modal" data-id="' . $row->getId() . '"'
                    . ' data-original="' . htmlentities((string)$detail['original']) . '"'
                    . ' data-current="' . htmlentities((string)$detail['current']) . '">'
                    . '<td>' . $field . '</td>'
                    . '<td>' . $original . '</td>'
                    . '<td>' . $current . '</td>'
                    . '</tr>';
            }

            $updates .= '</tbody></table>';
        }

        return $updates;
    }
}
