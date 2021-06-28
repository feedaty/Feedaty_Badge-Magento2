<?php
namespace Feedaty\Badge\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

class FeedatyTimeout implements ArrayInterface
{
    public function toOptionArray()
    {
        $return = [
            ["value" => "250","label" => __("250 ms")],
            ["value" => "500","label" => __("500 ms")],
            ["value" => "1000","label" => __("1000 ms")],
            ["value" => "2000","label" => __("2 s")],
            ["value" => "3000","label" => __("3 s")],
            ["value" => "20000","label" => __("20 s")],
            ["value" => "50000","label" => __("50 s")],
            ["value" => "100000","label" => __("100 s")]
        ];

        return $return;
    }
}
