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
    	$return = array(
    		array("value"=>"content","label"=>__("After Content")),
    		array("value"=>"catalog.product.related","label"=>__("Product Related")),
    		array("value"=>"productalert.price","label"=>__("After Price")),
    		array("value"=>"productalert.stock","label"=>__("After Stock Informations")),
    		array("value"=>"product.info.simple","label"=>__("Simple")),
    		array("value"=>"product.info.simple.extra.child0","label"=>__("Extra child")),
    		array("value"=>"product.info.addtocart","label"=>__("Add to cart")),
    		array("value"=>"product.description","label"=>__("Product description")),
    		array("value"=>"product.attributes","label"=>__("Product attributes")),
    		array("value"=>"product.info.upsell","label"=>__("Product upsell")),
    		array("value"=>"product.info.product_additional_data","label"=>__("Product additional")),
    		array("value"=>"product_tag_list","label"=>__("Product tag list"))
    	);
		return $return;
    }

}

