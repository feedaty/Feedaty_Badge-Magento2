<?php

namespace Feedaty\Badge\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

class PositionProduct implements ArrayInterface
{
    /**
    * Function toOptionArray 
    * 
    * @return array
    * return an array collection of products' positions ....
    */
    public function toOptionArray()
    {
        $return = [
            ["value"=>"content","label"=>__("After Content")],
            ["value"=>"catalog.product.related","label"=>__("Product Related")],
            ["value"=>"productalert.price","label"=>__("After Price")],
            ["value"=>"productalert.stock","label"=>__("After Stock Informations")],
            ["value"=>"product.info.simple","label"=>__("Simple")],
            ["value"=>"product.info.simple.extra.child0","label"=>__("Extra child")],
            ["value"=>"product.info.addtocart","label"=>__("Add to cart")],
            ["value"=>"product.description","label"=>__("Product description")],
            ["value"=>"product.attributes","label"=>__("Product attributes")],
            ["value"=>"product.info.upsell","label"=>__("Product upsell")],
            ["value"=>"product.info.product_additional_data","label"=>__("Product additional")],
            ["value"=>"product_tag_list","label"=>__("Product tag list")],
            ["value"=>"product.info.review","label"=>__("Product Info - Review")],
            ["value"=>"product.review.form","label"=>__("Product Review - Form")],
            ["value"=>"product.reviews","label"=>__("Product Reviews")]
            
        ];
        return $return;
    }
}
