<?php

namespace Feedaty\Badge\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Store\Model\StoreManagerInterface; 
use Feedaty\Badge\Helper\Data as DataHelp;
use \Magento\Framework\App\Request\Http;

class StyleProduct implements ArrayInterface
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

    /**
    *
    * @return $return
    */
    public function toOptionArray()
    {
        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $store = $this->storeManager->getStore($this->_request->getParam('store',0));
        $merchant_code = $store->getConfig('feedaty_global/feedaty_preferences/feedaty_code');

        if($this->_request->getParam('store', 0) == 0) 
        {
            $merchant_code = $this->scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', $store_scope);
        }

        if (strlen($this->scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', $store_scope)) == 0)
        {
           return array(); 
        } 

        $data = $this->_fdservice->_get_FeedatyData($merchant_code);
        foreach ($data as $k => $v) 
        {
            if ($v['type'] == "product") 
            {
                $return[] = ['value' => $k,'label' => ' <img src="'.$v['thumb'].'"><br />'];
            }
        }
        
        return $return;
    }
}
