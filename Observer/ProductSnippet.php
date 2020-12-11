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
        DataHelp $dataHelper,
        WebService $fdservice
        ) 
    {
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->_dataHelper = $dataHelper;
        $this->_fdservice = $fdservice;
    }

    /*
    *
    *
    */
    public function execute(\Magento\Framework\Event\Observer $observer) {

        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $block = $observer->getBlock();

        $fdSnipPos = $this->_scopeConfig->getValue('feedaty_microdata_options/snippet_products/product_position', $store_scope);


        $merchant = $this->_scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', $store_scope);

        $plugin_enabled = $this->_scopeConfig->getValue('feedaty_microdata_options/snippet_products/snippet_prod_enabled', $store_scope);

        if ( $observer->getElementName() == $fdSnipPos  ) 
        {
            if ($plugin_enabled != 0) 
            {
                $product = $this->registry->registry('current_product');

                if ($product !== null) 
                {
                    $sku= $product->getId();

                    $ratings = $this->_fdservice->getRatings($merchant, $sku, "product");

                    $snippet = 
                        '<div itemprop="aggregateRating" itemtype="http://schema.org/AggregateRating" itemscope>
                            <meta itemprop="reviewCount" content="' . $ratings['RatingsCount'] . '" />
                            <meta itemprop="ratingValue" content="' . $ratings['AvgRating'] . '" />
                        </div>';

                    $html = $observer->getTransport()->getOutput();

                    $html.= $snippet;

                    $observer->getTransport()->setOutput($html);
                }
            }
        }
    }
}
