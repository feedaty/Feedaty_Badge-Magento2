<?php

namespace Feedaty\Badge\Model\Config\Source;

use \Magento\Framework\Data\OptionSourceInterface;
use \Magento\Sales\Model\Order\Config;

class OrderStatuses implements OptionSourceInterface
{
    protected $orderConfig;


    /**
    * Constructor
    *
    */
    public function __construct(Config $orderConfig)
    {
         $this->orderConfig = $orderConfig;
    }

    /**
    *
    * @return $order_array
    */
    public function toOptionArray()
    {
        $statuses = $this->orderConfig->getStatuses();

        $order_array = array();
        foreach($statuses as $k=>$label)
        {
            $order_array[] = ['value' => $k,'label' => $label];
        }

        return $order_array;
    }
}
