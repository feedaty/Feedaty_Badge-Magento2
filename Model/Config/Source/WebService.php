<?php
namespace Feedaty\Badge\Model\Config\Source;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\StoreManagerInterface;
use Feedaty\Badge\Helper\Data as DataHelp;

class  WebService {

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Feedaty\Badge\Helper\Data
     */
    protected $_dataHelper;

    /*
    * Constructor
    *
    */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DataHelp $dataHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_dataHelper = $dataHelper;
    }


    /**
    * Function retrive_informations_product
    *
    * @param int $id
    *
    */
    public function retrive_informations_product($id) {

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $cache = $om->get('Magento\Framework\App\CacheInterface');

		$content = $cache->load("feedaty_product_".$id);
		
		if (!$content || strlen($content) == 0 || $content === "null") {
			$feedaty_code = $this->scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			
			$ch = curl_init();

            $resolver = $om->get('Magento\Framework\Locale\Resolver');
            $url = 'http://widget.zoorate.com/go.php?function=feed&action=ws&task=product&merchant_code='.$feedaty_code.'&ProductID='.$id.'&language='.$resolver->getLocale();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, '3');
			$content = trim(curl_exec($ch));
			curl_close($ch);
			
			if (strlen($content) > 0)
			$cache->save($content, "feedaty_product_".$id, array("feedaty_cache"), 3*60*60); // 3 hours of cache
		}
		
		$data = json_decode($content,true);
		
		return $data;
	}
	

    /**
    * Function retrive_informations_store
    * @return $data
    */
	public function retrive_informations_store() {

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $cache = $om->get('Magento\Framework\App\CacheInterface');

		$content = $cache->load("feedaty_store");
		
		if (!$content || strlen($content) < 5 || $content === "null") {
			$feedaty_code = $this->scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

			$ch = curl_init();
            $url = 'http://widget.zoorate.com/go.php?function=feed&action=ws&task=merchant&merchant_code='.$feedaty_code;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, '3');
			$content = trim(curl_exec($ch));
			curl_close($ch);

			if (strlen($content) > 0)
			$cache->save($content, "feedaty_store", array("feedaty_cache"), 3*60*60); // 3 hours of cache
		}
		
		$data = json_decode($content,true);
    
		return $data;
	}


	/**
    * Function send_order 
    *
    * @param object $data
    * 
    */
	public static function send_order($data) {
		$ch = curl_init();
        $url = 'http://www.zoorate.com/ws/feedatyapi.svc/SubmitOrders';

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, '60');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		$content = trim(curl_exec($ch));

		curl_close($ch);
	}


    /**
    * Function _get_FeedatyData
    *
    * @return $data
    */
    public function _get_FeedatyData() {

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $cache = $om->get('Magento\Framework\App\CacheInterface');

        $content = $cache->load("feedaty_store");

        //i think this may cause a loop somewhere
        WebService::send_notification($this->scopeConfig,$this->storeManager,$this->_dataHelper);

        $feedaty_code = $this->scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $resolver = $om->get('Magento\Framework\Locale\Resolver');

        $string = "FeedatyData".$feedaty_code.$resolver->getLocale();
        $content =$cache->load($string);

		if (!$content || strlen($content) == 0 || $content === "null") {
            $ch = curl_init();
            $url = 'http://widget.zoorate.com/go.php?function=feed_be&action=widget_list&merchant_code='.$feedaty_code.'&language='.$resolver->getLocale();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, '60');
            $content = trim(curl_exec($ch));
            curl_close($ch);

            $cache->save($content, "FeedatyData".$feedaty_code.$resolver->getLocale(), array("feedaty_cache"), 24*60*60); // 24 hours of cache
        }

        $data = json_decode($content,true);
        return $data;
    }

    /**
    * Function send_notification
    *
    * @param object $_scopeConfig
    * @param object $_storeManager
    * @param object $_dataHelper
    */
    public static function send_notification($_scopeConfig,$_storeManager,$_dataHelper) {


        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $cache = $om->get('Magento\Framework\App\CacheInterface');

        $content = $cache->load("feedaty_notification");

        $cnt = $_scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)."-".$_scopeConfig->getValue('feedaty_badge_options/widget_store/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)."-".$_scopeConfig->getValue('feedaty_badge_options/widget_products/product_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($content != $cnt) {
            $store = $_storeManager->getStore();

            $ver = json_decode(json_encode($_dataHelper->getExtensionVersion()),true);

            $prodMetadata = $om->get('Magento\Framework\App\ProductMetadataInterface');

            $fdata['keyValuePairs'][] = array("Key" => "Platform", "Value" => "Magento ".$prodMetadata->getVersion());
            $fdata['keyValuePairs'][] = array("Key" => "Version", "Value" => (string) $_dataHelper->getExtensionVersion());
            $fdata['keyValuePairs'][] = array("Key" => "Url", "Value" => $_storeManager->getStore()->getBaseUrl());
            $fdata['keyValuePairs'][] = array("Key" => "Os", "Value" => PHP_OS);
            $fdata['keyValuePairs'][] = array("Key" => "Php Version", "Value" => phpversion());
            $fdata['keyValuePairs'][] = array("Key" => "Name", "Value" => $store->getName());
            $fdata['keyValuePairs'][] = array("Key" => "Action", "Value" => "Enabled");
            $fdata['keyValuePairs'][] = array("Key" => "Position_Merchant", "Value" => $_scopeConfig->getValue('feedaty_badge_options/widget_store/store_position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $fdata['keyValuePairs'][] = array("Key" => "Position_Product", "Value" => $_scopeConfig->getValue('feedaty_badge_options/widget_products/product_position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $fdata['keyValuePairs'][] = array("Key" => "Status", "Value" => $_scopeConfig->getValue('feedaty_global/sendorder/sendorder', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $fdata['merchantCode'] = $_scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $ch = curl_init();

            $url = 'http://www.zoorate.com/ws/feedatyapi.svc/SetPluginKeyValue';

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, '60');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($fdata));
            curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json','Expect:'));
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            $content = trim(curl_exec($ch));

            curl_close($ch);
            
            $cache->save($cnt, "feedaty_notification", array("feedaty_cache"), 10*24*60*60);
        }
    }
}	