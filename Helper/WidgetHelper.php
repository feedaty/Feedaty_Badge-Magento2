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
    private WebService $service;
    private Http $request;
    private StoreManagerInterface $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Data $dataHelper
     * @param Http $request
     * @param WebService $service
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DataHelp $dataHelper,
        Http $request,
        WebService $service
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->request = $request;
        $this->service = $service;
    }

    /**
     *
     * @return $return
     */
    public function badgeData($type)
    {

        $return =  array();

        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $store = $this->storeManager->getStore($this->request->getParam('store',0));

        $merchant_code = $store->getConfig('feedaty_global/feedaty_preferences/feedaty_code');

        if ($this->request->getParam('store', 0) == 0) {

            $merchant_code = $this->scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', $store_scope);

        }

        if (strlen($merchant_code) == 0) {

            return array();

        }

        $dataObject = $this->service->getFeedatyData($merchant_code);

        $data = $dataObject[$type]['variants'];

        if($data) {

            foreach ($data as $k => $v)  {

                $return[] = ['value' => $k,'label' => $v['name_shown_it-IT']];

            }

        }

        return $return;
    }
}
