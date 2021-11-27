<?php

namespace Feedaty\Badge\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;
use Feedaty\Badge\Helper\WidgetHelper;

class StyleProductTab implements ArrayInterface
{

    /**
     * @var WidgetHelper
     */
    protected $_widgetHelper;

    /**
     * @param WidgetHelper $widgetHelper
     */
    public function __construct(WidgetHelper $widgetHelper)
    {
        $this->_widgetHelper = $widgetHelper;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_widgetHelper->badgeData('product_tab');
    }
}
