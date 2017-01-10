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

    protected $_blockFactory;

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
    protected $_registry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */   
    protected $_objectManager;

    /**
     * @var Feedaty\Badge\Helper\Data
     */   
    protected $_dataHelper;

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
        DataHelp $dataHelper
    ) {
        $this->_blockFactory = $blockFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_registry = $registry;
        $this->_backendUrl = $backendUrl;
        $this->_objectManager = $objectmanager;
        $this->_dataHelper = $dataHelper;
    }

	public function execute(Observer $observer) {

        $webservice = new WebService($this->_scopeConfig, $this->storeManager, $this->_dataHelper);
		
		$block = $observer->getBlock();

		$transport = $observer->getTransport();

		$html = $transport->getHtml();

		if ($observer->getElementName() ==$this->_scopeConfig->getValue('feedaty_badge_options/review_products/product_position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {


			$plugin_enabled = $this->_scopeConfig->getValue('feedaty_badge_options/review_products/product_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	
			$product = $this->_registry->registry('current_product');
			
			if($plugin_enabled!=0 && !is_null($product)){
                $product = $product->getId();

				$toview['data_review'] = $webservice->retrive_informations_product($product);

				if ($this->_scopeConfig->getValue('feedaty_badge_options/review_products/order_review', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 1)
					$toview['data_review']['Feedbacks'] = array_reverse($toview['data_review']['Feedbacks']);
				
				$toview['count_review'] = $this->_scopeConfig->getValue('feedaty_badge_options/review_products/count_review', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
				$toview['link'] = '<a href="'.$toview['data_review']['Product']['Url'].'">'.__('Read all reviews').'</a>';
		

				if (count($toview['data_review']['Feedbacks']) > 0) {

					$html = $observer->getTransport()->getOutput();

                    

					$buttons = $this->_objectManager->create('Feedaty\Badge\Block\Product', array('template'=>'Feedaty_Badge::product_reviews.phtml'))->setData('view', $toview)->setTemplate('Feedaty_Badge::product_reviews.phtml'); 
                    
					$html .= $buttons->toHtml();


					$observer->getTransport()->setOutput($html);
				}
			}
		}
	}
}