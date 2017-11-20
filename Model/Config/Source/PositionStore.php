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
        $return = [
            ["value"=>"cms_page","label"=>__("Position Cms page")],
            ["value"=>"page_content_heading","label"=>__("Position Page content heading")],
            ["value"=>"cart_sidebar","label"=>__("Position Cart sidebar")],
            ["value"=>"wishlist_sidebar","label"=>__("Position Wishlist sidebar")],
            ["value"=>"right.reports.product.viewed","label"=>__("Position Right product viewed")],
            ["value"=>"right.reports.product.compared","label"=>__("Position Right product compared")],
            ["value"=>"right.poll","label"=>__("Position Right poll")],
            ["value"=>"right","label"=>__("Position Right")],
            ["value"=>"left","label"=>__("Position Left")],
            ["value"=>"footer","label"=>__("Position Footer")],
            ["value"=>"bottom.container","label"=>__("Position Bottom container")],
            ["value"=>"footer_links","label"=>__("Position Footer links")]
        ];
        
        return $return;
    }
}

