<?php
namespace Feedaty\Badge\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PositionStore implements ArrayInterface
{
    /**
    *
    * @return $return
    */
    public function toOptionArray()
    {
    	$return = array(
    		array("value"=>"cms_page","label"=>__("Position Cms page")),
    		array("value"=>"page_content_heading","label"=>__("Position Page content heading")),
    		array("value"=>"cart_sidebar","label"=>__("Position Cart sidebar")),
    		array("value"=>"wishlist_sidebar","label"=>__("Position Wishlist sidebar")),
    		array("value"=>"right.reports.product.viewed","label"=>__("Position Right product viewed")),
    		array("value"=>"right.reports.product.compared","label"=>__("Position Right product compared")),
    		/*array("value"=>"right.permanent.callout","label"=>Mage::helper('core')->__("Position Right permanent callout")),*/
    		array("value"=>"right.poll","label"=>__("Position Right poll")),
    		array("value"=>"right","label"=>__("Position Right")),
			array("value"=>"left","label"=>__("Position Left")),
            array("value"=>"footer","label"=>__("Position Footer")),
    		array("value"=>"bottom.container","label"=>__("Position Bottom container")),
            array("value"=>"footer_links","label"=>__("Position Footer links"))
    	);
		
		return $return;
        //TODO: fix page_content_heading
    }
}

