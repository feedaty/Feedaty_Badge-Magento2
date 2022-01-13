<?php

namespace Feedaty\Badge\Model;

/**
 * @method \Feedaty\Badge\Model\ResourceModel\Order getResource()
 * @method \Feedaty\Badge\Model\ResourceModel\Order\Collection getCollection()
 */
class Order extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'feedaty_badge_order';
    protected $_cacheTag = 'feedaty_badge_order';
    protected $_eventPrefix = 'feedaty_badge_order';

    protected function _construct()
    {
        $this->_init('Feedaty\Badge\Model\ResourceModel\Order');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
