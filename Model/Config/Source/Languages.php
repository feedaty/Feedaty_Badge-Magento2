<?php
namespace Feedaty\Badge\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

class Languages implements ArrayInterface
{
    public function toOptionArray()
    {
        $return = [
        	["value" => "0","label" => __("All")],
            ["value" => "en-US","label" => __("English")],
            ["value" => "it-IT","label" => __("Italian")],
            ["value" => "de-DE","label" => __("Deuthc")],
            ["value" => "fr-FR","label" => __("Francais")],
            ["value" => "es-ES","label" => __("Espanian")]
        ];
        
        return $return;
    }
}
