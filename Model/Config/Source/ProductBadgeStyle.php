<?php
namespace Feedaty\Badge\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

class ProductBadgeStyle implements ArrayInterface
{
    public function toOptionArray()
    {
        $return = [
        	["value" => "product","label" => __("Product Badge Style")],
            ["value" => "carouselproduct","label" => __("Product Carousel Badge Style")],
            ["value" => "product_tab","label" => __("Product Tab Badge Style")],
        ];

        return $return;
    }
}
