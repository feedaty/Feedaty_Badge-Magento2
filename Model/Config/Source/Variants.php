<?php
namespace Feedaty\Badge\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Store\Model\StoreManagerInterface; 
use \Magento\Framework\App\Request\Http;
use Feedaty\Badge\Helper\Data as DataHelp;

class Variants implements ArrayInterface
{
       /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;

    /**
    * @var Feedaty\Badge\Helper\Data
    */
    protected $dataHelper;

    /*
    * Constructor
    *
    */
    public function __construct(
        ScopeConfigInterface $scopeConfig, 
        StoreManagerInterface $storeManager,
        DataHelp $dataHelper,
        Http $request,
        WebService $fdservice
        ) 
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_dataHelper = $dataHelper;
        $this->_request = $request;
        $this->_fdservice = $fdservice;
    }

    public function toOptionArray()
    {
        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $store = $this->storeManager->getStore($this->_request->getParam('store', 0));
        $merchant_code = $store->getConfig('feedaty_global/feedaty_preferences/feedaty_code');
        $plugin_enabled = $this->scopeConfig->getValue('feedaty_badge_options/widget_store/enabled', $store_scope);
        $badge_style = $this->scopeConfig->getValue('feedaty_badge_options/widget_store/badge_style', $store_scope);

        if($this->_request->getParam('store', 0) == 0) 
        {
            $merchant_code = $this->scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', $store_scope);
        }

        if (strlen($merchant_code == 0))  
        {
            return array();
        }

        $data = $this->_fdservice->getFeedatyData($merchant_code);

        foreach ($data as $k => $v) {

            if ($k == $badge_style) 
            {
                foreach ($v['variants'] as $key => $value) {

                    $return[] = ['value' => $key,'label' => $value];
                }
                
            }
        }

        return $return;

    }
}
