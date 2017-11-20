<?php
namespace Feedaty\Badge\Observer;

use Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Backend\Model\UrlInterface;
use \Magento\Framework\Registry;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Feedaty\Badge\Helper\Data as DataHelp;
use \Magento\Framework\View\Element\BlockFactory;

class ProductReviews implements ObserverInterface
{

    protected $blockFactory;

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

    /**
    * @var \Magento\Framework\ObjectManagerInterface
    */   
    protected $objectManager;

    /**
    * @var Feedaty\Badge\Helper\Data
    */   
    protected $dataHelper;

    /*
    * Constructor
    *
    */
    public function __construct(
        BlockFactory $blockFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Registry $registry,
        UrlInterface $backendUrl,
        ObjectManagerInterface $objectmanager,
        DataHelp $dataHelper,
        WebService $fdservice
        ) 
    {
        $this->_blockFactory = $blockFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_registry = $registry;
        $this->_backendUrl = $backendUrl;
        $this->_objectManager = $objectmanager;
        $this->_dataHelper = $dataHelper;
        $this->_fdservice = $fdservice;
    }

    public function execute(Observer $observer) {

        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $block = $observer->getBlock();
        $transport = $observer->getTransport();
        $html = $transport->getHtml();
        $merchant = $this->_scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', $store_scope);
        $prod_pos = $this->_scopeConfig->getValue('feedaty_badge_options/review_products/product_position', $store_scope);
        $plugin_enabled = $this->_scopeConfig->getValue('feedaty_badge_options/review_products/product_enabled',  $store_scope);
        $show_reviews = $this->_scopeConfig->getValue('feedaty_badge_options/review_products/count_review', $store_scope);

        if ($observer->getElementName() == $prod_pos) 
        {
            $product = $this->_registry->registry('current_product');
            
            if ($plugin_enabled != 0 && $product !== null) 
            {
                $product = $product->getId();

                $toview['data_review'] = $this->_fdservice->retriveInformationsProduct($merchant, $product);

                if ($this->_scopeConfig->getValue('feedaty_badge_options/review_products/order_review', $store_scope) == 1) 
                {
                    $toview['data_review']['Feedbacks'] = array_reverse($toview['data_review']['Feedbacks']);
                }
                
                $toview['count_review'] = $show_reviews;
                $toview['link'] = '<a href="'.$toview['data_review']['Product']['Url'].'">'.__('Read all reviews').'</a>';
        
                if (!empty($toview['data_review']['Feedbacks'])) 
                {
                    $html = $observer->getTransport()->getOutput();
                    $buttons = $this->_objectManager->create('Feedaty\Badge\Block\Product', array('template'=>'Feedaty_Badge::product_reviews.phtml'))->setData('view', $toview)->setTemplate('Feedaty_Badge::product_reviews.phtml'); 
                    
                    $html .= $buttons->toHtml();
                    $observer->getTransport()->setOutput($html);
                }
            }
        }
    }
}
