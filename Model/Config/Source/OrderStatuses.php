<?php

namespace Feedaty\Badge\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;
use \Magento\Sales\Model\Order\Config;

class OrderStatuses implements ArrayInterface
{

    /**
    * Constructor
    * 
    */
    public function __construct(Config $orderConfig) 
    {
         $this->_orderConfig = $orderConfig;
    }

    /**
    * 
    * @return $order_array
    */
    public function toOptionArray()
    {
        $statuses = $this->_orderConfig->getStatuses();
        
        $order_array = array();
        foreach($statuses as $k=>$label) 
        {
            $order_array[] = ['value' => $k,'label' => $label];
        }

        return $order_array;
    }
}
