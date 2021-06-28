<?php

namespace Feedaty\Badge\Block\Widget;

use Feedaty\Badge\Helper\ConfigRules;
use Feedaty\Badge\Helper\Data as DataHelp;
use Feedaty\Badge\Model\Config\Source\WebService;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;

class ProductBadge extends Template implements BlockInterface
{
    protected $_template = "widget/product-bagde.phtml";

    /**
     * @var WebService
     */
    protected $_webservice;

    /**
     * @var ConfigRules
     */
    protected $_configRules;

    /**
     * @var DataHelp
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    public function __construct(
        Context $context,
        DataHelp $dataHelper,
        WebService $webservice,
        ConfigRules $configRules,
        \Magento\Framework\Registry $registry
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_webservice = $webservice;
        $this->_configRules = $configRules;
        $this->_registry = $registry;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function getFeedatyCode()
    {
        return $this->_configRules->getFeedatyCode();
    }

    public function getFeedatyData($merchantCode)
    {
        return $this->_webservice->getFeedatyData($merchantCode);
    }

    public function getExtensionVersion()
    {
        return json_decode(json_encode($this->_dataHelper->getExtensionVersion()), true);
    }

    /**
     * @return mixed|null
     */
    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

}
