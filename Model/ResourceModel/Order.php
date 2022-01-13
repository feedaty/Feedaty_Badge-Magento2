<?php

namespace Feedaty\Badge\Model\ResourceModel;

class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('feedaty_orders', 'feedaty_orders_id');
    }

}
