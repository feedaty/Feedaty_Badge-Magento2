<?php
namespace Feedaty\Badge\Observer;

use Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Registry ;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Event\Observer;
use Feedaty\Badge\Helper\Data as DataHelp;

class StoreSnippet implements ObserverInterface
{

    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;

    /**
    * @var \Magento\Framework\Registry
    */
    protected $registry;

    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $storeManager;

    /**
    * @var Feedaty\Badge\Helper\Data
    */
    protected $dataHelper;

    /*
    *
    * Constructor
    *
    */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Registry $registry,
        StoreManagerInterface $storeManager,
        DataHelp $dataHelper,
        WebService $fdservice
        ) 
    {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->_dataHelper = $dataHelper;
        $this->_fdservice = $fdservice;
    }

    /**
    *
    * Execute
    * @param $observer
    */
    public function execute(Observer $observer) {

        $block = $observer->getBlock();
        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $fdWidStorePos = $this->scopeConfig->getValue('feedaty_badge_options/widget_store/store_position', $store_scope);
        $fdSnipStorPos = $this->scopeConfig->getValue('feedaty_microdata_options/snippet_store/store_position', $store_scope);
        $merchant = $this->scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', $store_scope);
        $plugin_enabled = $this->scopeConfig->getValue('feedaty_microdata_options/snippet_store/snippet_enabled', $store_scope);

        if ($observer->getElementName()== $fdSnipStorPos && $fdSnipStorPos != $fdWidStorePos) 
        {
            if ($plugin_enabled != 0)
            {
                $html = $this->_fdservice->getMerchantRichSnippet($merchant);
                $observer->getTransport()->setOutput($html);
            }
        }
    }
}
