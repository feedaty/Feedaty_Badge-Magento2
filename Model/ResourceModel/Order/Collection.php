<?php

namespace Feedaty\Badge\Model\ResourceModel\Order;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'feedaty_orders_id';


    protected function _construct()
    {
        $this->_init('Feedaty\Badge\Model\Order', 'Feedaty\Badge\Model\ResourceModel\Order');
    }

}
