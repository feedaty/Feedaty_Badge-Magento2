<?php
namespace Feedaty\Badge\Observer;

use Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Registry ;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Event\Observer;
use Feedaty\Badge\Helper\Data as DataHelp;

class StoreBadge implements ObserverInterface
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
        $plugin_enabled = $this->scopeConfig->getValue('feedaty_badge_options/widget_store/enabled', $store_scope);
        $badge_style = $this->scopeConfig->getValue('feedaty_badge_options/widget_store/badge_style', $store_scope);

        if ($observer->getElementName() == $fdWidStorePos) 
        {
            if (rand(1,3000) === 2000) 
            {
                $this->_fdservice->send_notification($this->scopeConfig,$this->storeManager,$this->_dataHelper);
            }
            
            if ($plugin_enabled != 0)
            {
                $data = $this->_fdservice->_get_FeedatyData($merchant);
                $ver = json_decode(json_encode($this->_dataHelper->getExtensionVersion()),true);

                $html = '<!-- PlSMa '.$ver[0].' -->'.$data[$badge_style]['html_embed'].$observer->getTransport()->getOutput();

                if ($fdWidStorePos == $fdSnipStorPos) 
                {
                    $html.= $this->_fdservice->getMerchantRichSnippet($merchant);
                }

                $observer->getTransport()->setOutput($html);

            }
        }
    }
}
