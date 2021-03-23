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
    public function execute( Observer $observer ) {

        $zoorate_env = "widget.zoorate.com";

        $block = $observer->getBlock();

        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $fdWidStorePos = $this->scopeConfig->getValue(
            'feedaty_badge_options/widget_store/merch_position', $store_scope
        );

        $fdSnipStorPos = $this->scopeConfig->getValue(
            'feedaty_microdata_options/snippet_store/merch_snip_position', $store_scope
        );

        $merchant = $this->scopeConfig->getValue(
            'feedaty_global/feedaty_preferences/feedaty_code', $store_scope
        );

        $plugin_enabled = $this->scopeConfig->getValue(
            'feedaty_badge_options/widget_store/merch_enabled', $store_scope
        );

        $badge_style = $this->scopeConfig->getValue(
            'feedaty_badge_options/widget_store/merch_style', $store_scope
        );

        $variant = $this->scopeConfig->getValue(
            'feedaty_badge_options/widget_store/merch_variant', $store_scope
        );

        $guilang = $this->scopeConfig->getValue(
            'feedaty_badge_options/widget_store/merch_guilang', $store_scope
        );

        $rvlang = $this->scopeConfig->getValue(
            'feedaty_badge_options/widget_store/merch_rvlang', $store_scope
        );

        if ( $observer->getElementName() == $fdWidStorePos ) 
        {
            
            if ($plugin_enabled != 0)
            {

                $data = $this->_fdservice->getFeedatyData( $merchant );

                if( count($data) > 0 ) {

                    $ver = json_decode( json_encode( $this->_dataHelper->getExtensionVersion() ), true );

                    $widget = $data[$badge_style];

                    $name = $widget["name"];

                    $variant = $widget["variants"][$variant];

                    $rvlang = $rvlang ? $rvlang : "all";

                    $guilang = $guilang ? $guilang : "it-IT";

                    $widget['html'] = str_replace("ZOORATE_SERVER", $zoorate_env, $widget['html']);
                    $widget['html'] = str_replace("VARIANT", $variant, $widget['html']);
                    $widget['html'] = str_replace("GUI_LANG", $guilang, $widget['html']);
                    $widget['html'] = str_replace("REV_LANG", $rvlang, $widget['html']);
                
                    $element = htmlspecialchars_decode($widget["html"]);

                    $html = $observer->getTransport()->getOutput();
                    $html.= "<!-- PlPMa ".$ver[0]." -->".$element;

                    $observer->getTransport()->setOutput($html);

                }

            }
        }
    }
}
