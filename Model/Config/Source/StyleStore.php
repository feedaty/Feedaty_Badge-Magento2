<?php
namespace Feedaty\Badge\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Store\Model\StoreManagerInterface; 
use \Magento\Framework\App\Request\Http;
use Feedaty\Badge\Helper\Data as DataHelp;

class StyleStore implements ArrayInterface
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
    public function toOptionArray() {

        $retun = array();

        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $store = $this->storeManager->getStore($this->_request->getParam('store', 0));

        $merchant_code = $store->getConfig('feedaty_global/feedaty_preferences/feedaty_code');

        if($this->_request->getParam('store', 0) == 0) {

            $merchant_code = $this->scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', $store_scope);

        }

        if (strlen($merchant_code == 0))  {

            return $return;

        }

        $data = $this->_fdservice->getFeedatyData($merchant_code);

        if($data) {

            // get Badges
            foreach ($data as $k => $v) {

                if ($v['type'] == "merchant" && $v['name'] != "dynamic" ) {

                    $return[] = ['value' => $k,'label' => $v['name']];

                }

            }

        }

        return $return;
    }
}
