<?php
namespace Feedaty\Badge\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Store\Model\StoreManagerInterface;
use Feedaty\Badge\Helper\Data as DataHelp;
use \Magento\Framework\App\Request\Http;

class WidgetHelper extends \Magento\Framework\App\Helper\AbstractHelper
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
    public function badgeData($type)
    {

        $return =  array();

        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $store = $this->storeManager->getStore($this->_request->getParam('store',0));

        $merchant_code = $store->getConfig('feedaty_global/feedaty_preferences/feedaty_code');

        if ($this->_request->getParam('store', 0) == 0) {

            $merchant_code = $this->scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', $store_scope);

        }

        if (empty($merchant_code)) {

            return array();

        }

        $dataObject = $this->_fdservice->getFeedatyData($merchant_code);

        $data = $dataObject[$type]['variants'];

        if($data) {

            foreach ($data as $k => $v)  {

                $return[] = ['value' => $k,'label' => $v['name_shown_it-IT']];

            }

        }

        return $return;
    }
}
