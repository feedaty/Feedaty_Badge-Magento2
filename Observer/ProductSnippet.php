<?php
namespace Feedaty\Badge\Observer;

use Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Registry;
use Feedaty\Badge\Helper\Data as DataHelp;


class ProductSnippet implements ObserverInterface
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
        DataHelp $dataHelper

    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->_dataHelper = $dataHelper;
    }

    /*
    *
    *
    */
    public function execute(\Magento\Framework\Event\Observer $observer) {

        $webservice = new WebService($this->_scopeConfig, $this->storeManager, $this->_dataHelper);

        $block = $observer->getBlock();
        
        $fdSnipPos = $this->_scopeConfig->getValue('feedaty_microdata_options/snippet_products/product_position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $fdWidgetPos = $this->_scopeConfig->getValue('feedaty_badge_options/widget_products/product_position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($observer->getElementName()== $fdSnipPos && $fdSnipPos != $fdWidgetPos  ) {

            $plugin_enabled = $this->_scopeConfig->getValue('feedaty_microdata_options/snippet_products/snippet_prod_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            if($plugin_enabled!=0) {

                $product = $this->registry->registry('current_product');

                if (!is_null($product)) {

                    $product = $product->getId();
                    $html = $webservice->getProductRichSnippet($product);

                    $observer->getTransport()->setOutput($html);
                }
            }
        }
	}
}