<?php
namespace Feedaty\Badge\Block;

use \Magento\Framework\View\Element\Template;

class Product extends Template implements \Magento\Widget\Block\BlockInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Feedaty_Badge::base.phtml');
    }
}
