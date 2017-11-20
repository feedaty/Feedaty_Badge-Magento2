<?php
namespace Feedaty\Badge\Observer;

use Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Registry;
use Feedaty\Badge\Helper\Data as DataHelp;

class ProductBadge implements ObserverInterface
{

    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;

    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $storeManager;

    /**
    * @var \Magento\Framework\Registry
    */
    protected $registry;

    /*
    * Constructor
    */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Registry $registry,
        DataHelp $dataHelper,
        WebService $fdservice
        ) 
    {
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->_dataHelper = $dataHelper;
        $this->_fdservice = $fdservice;
    }

    /*
    *
    *
    */
    public function execute(\Magento\Framework\Event\Observer $observer) {

        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $block = $observer->getBlock();
        $fdWidgetPos = $this->_scopeConfig->getValue('feedaty_badge_options/widget_products/product_position', $store_scope);
        $fdSnipPos = $this->_scopeConfig->getValue('feedaty_microdata_options/snippet_products/product_position', $store_scope);
        $merchant = $this->_scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', $store_scope);
        $badge_style = $this->_scopeConfig->getValue('feedaty_badge_options/widget_products/badge_style', $store_scope);
        $plugin_enabled = $this->_scopeConfig->getValue('feedaty_badge_options/widget_products/product_enabled', $store_scope);

        if ($observer->getElementName() == $fdWidgetPos) 
        {
            if ($plugin_enabled != 0) 
            {
                $product = $this->registry->registry('current_product');

                if ($product !== null) 
                {
                    $product = $product->getId();
                    $data = $this->_fdservice->_get_FeedatyData($merchant);
                    $ver = json_decode(json_encode($this->_dataHelper->getExtensionVersion()), true);

                    $html = '<!-- PlPMa '.$ver[0].' -->'.str_replace("__insert_ID__", "$product",$data[$badge_style]['html_embed']).$observer->getTransport()->getOutput();

                    if ($fdWidgetPos == $fdSnipPos) 
                    {
                        $html.= $this->_fdservice->getProductRichSnippet($merchant, $product);
                    } 

                    $observer->getTransport()->setOutput($html);
                }
            }
        }
    }
}
