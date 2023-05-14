<?php
namespace Feedaty\Badge\Model\Config\Source;

use AllowDynamicProperties;
use \Magento\Framework\Option\ArrayInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Request\Http;
use Feedaty\Badge\Helper\Data as DataHelp;

#[AllowDynamicProperties] class Variants implements ArrayInterface
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
    private StoreManagerInterface $storeManager;
    private Http $request;

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
        $this->dataHelper = $dataHelper;
        $this->request = $request;
        $this->fdservice = $fdservice;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function toOptionArray()
    {
        $return = array();

        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $store = $this->storeManager->getStore($this->_request->getParam('store', 0));
        $merchant_code = $store->getConfig('feedaty_global/feedaty_preferences/feedaty_code');
        $plugin_enabled = $this->scopeConfig->getValue('feedaty_badge_options/widget_store/merch_enabled', $store_scope);
        $badge_style = $this->scopeConfig->getValue('feedaty_badge_options/widget_store/merch_style', $store_scope);

        if($this->_request->getParam('store', 0) == 0)
        {
            $merchant_code = $this->scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', $store_scope);
        }

        if (strlen($merchant_code == 0))
        {
            $return = array();
        }

        $data = $this->_fdservice->getFeedatyData($merchant_code);

        if($data && $data != null) {

            foreach ($data as $k => $v) {

                if ($k == $badge_style) {

                    foreach ($v['variants'] as $key => $value) {

                        $return[] = ['value' => $key,'label' => $value];

                    }

                }

            }

        }
        else {

            $return = array();

        }

        return $return;

    }
}
