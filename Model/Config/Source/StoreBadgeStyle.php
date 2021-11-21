<?php
namespace Feedaty\Badge\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

class StoreBadgeStyle implements ArrayInterface
{
    public function toOptionArray()
    {
        $return = [
        	["value" => "merchant","label" => __("Merchant Bagde Style")],
            ["value" => "carousel","label" => __("Carousel Bagde Style")]
        ];

        return $return;
    }
}
