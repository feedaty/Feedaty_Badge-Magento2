<?php
namespace Feedaty\Badge\Model\Config\Source;

use \Magento\Framework\HTTP\Client\Curl;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\StoreManagerInterface;
use Feedaty\Badge\Helper\Data as DataHelp;
use \Magento\Framework\ObjectManagerInterface;

class WebService 
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
    * @var Feedaty\Badge\Helper\Data
    */
    protected $dataHelper;

    /**
    * @var \Magento\Framework\ObjectManagerInterface
    */   
    protected $objectManager;

    /*
    * Constructor
    *
    */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DataHelp $dataHelper,
        Curl $curl,
        ObjectManagerInterface $objectmanager
        ) 
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_dataHelper = $dataHelper;
        $this->_curl = $curl;
        $this->_objectManager = $objectmanager;
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, 1);
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->_curl->setOption(CURLOPT_VERBOSE, true);
    }

    /**
    * Function getReqToken - get the request token
    *  
    * @return $response
    *
    */
    private function getReqToken(){
        
        $url = "http://api.feedaty.com/OAuth/RequestToken";
        $this->_curl->addHeader('Content-Type','application/x-www-form-urlencoded');
        $this->_curl->get($url);

        $response = json_decode($this->_curl->getBody());

        return $response;
    }

    /**
    * Function serializeData - serialize data to send 
    * 
    * @param $fields
    *
    * @return $dati
    */
    private function serializeData($fields){
        $data = '';
        foreach($fields as $k => $v){
            $data .= $k . '=' . urlencode($v) . '&';
        }
        $data = rtrim($data, '&');
        return $data;
    }

    /**
    * Function getAccessToken - get the access token
    *
    * @param $token
    *
    * @return $response - the access token
    */
    private function getAccessToken($token,$merchant,$secret) {

        $encripted_code = $this->encryptToken($token,$merchant,$secret);

        $url = "http://api.feedaty.com/OAuth/AccessToken";
        $this->_curl->addHeader("Content-Type","application/x-www-form-urlencoded");
        $this->_curl->addHeader("Authorization", "Basic " . $encripted_code);
        $this->_curl->addHeader("User-Agent","Mage2");

        $fields = [
            'oauth_token' => $token->RequestToken,
            'grant_type'=>'authorization'
        ];

        $this->_curl->post($url,$fields);

        $response = $this->_curl->getBody();

        return $response;
    }

    /**
    * Function encryptToken
    *
    * @param $token
    * @param $merchant
    * @param $secret
    *
    * @return $base64_sha_token - the encrypted token
    */
    private function encryptToken($token, $merchant, $secret){
        $sha_token = sha1($token->RequestToken.$secret);
        $base64_sha_token = base64_encode($merchant.":".$sha_token);
        return $base64_sha_token;   
    }

    /**
    * Function retriveInformationsProduct
    *
    * @param int $id
    *
    */
    public function retriveInformationsProduct($feedaty_code, $id) {

        $cache = $this->_objectManager->get('Magento\Framework\App\CacheInterface');

        $content = $cache->load("feedaty_product_".$id);
        
        if (!$content || strlen($content) == 0 || $content === "null") 
        {
            $ch = curl_init();

            $resolver = $this->_objectManager->get('Magento\Framework\Locale\Resolver');
            $url = 'http://widget.zoorate.com/go.php?function=feed&action=ws&task=product&merchant_code='.$feedaty_code.'&ProductID='.$id.'&language='.$resolver->getLocale();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, '3');
            $content = trim(curl_exec($ch));
            curl_close($ch);
            
            if (strlen($content) > 0) 
            {
                // 3 hours of cache
                $cache->save($content, "feedaty_product_".$id, array("feedaty_cache"), 3*60*60);
            }
        }
        
        $data = json_decode($content, true);
        
        return $data;
    }

    /**
    * Function retrive_informations_store
    * @return $data
    */
    public function retrive_informations_store($feedaty_code) {

        $cache = $this->_objectManager->get('Magento\Framework\App\CacheInterface');

        $content = $cache->load("feedaty_store");
        
        if (!$content || strlen($content) < 5 || $content === "null") 
        {
            $ch = curl_init();
            $url = 'http://widget.zoorate.com/go.php?function=feed&action=ws&task=merchant&merchant_code='.$feedaty_code;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, '3');
            $content = trim(curl_exec($ch));
            curl_close($ch);

            if (strlen($content) > 0)
            {
                // 3 hours of cache
                $cache->save($content, "feedaty_store", array("feedaty_cache"), 3*60*60);
            }
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
    public function send_order($merchant, $secret, $data) {

        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $ch = curl_init();
        $url = 'http://api.feedaty.com/Orders/Insert';

        $token = $this->getReqToken();
        $accessToken =json_decode($this->getAccessToken($token, $merchant, $secret));

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, '60');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER,['Content-Type: application/json', 'Authorization: Oauth '.$accessToken->AccessToken]);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $content = trim(curl_exec($ch));

        curl_close($ch);

        $fdDebugEnabled = $this->_scopeConfig->getValue('feedaty_global/debug/debug_enabled', $store_scope);
        if($fdDebugEnabled != 0) {
            $message = json_encode($content);
            $this->feedatyDebug($message, "ORDER RESPONSE INFO");
        }
    }

    /**
    * Function getProductRichSnippet 
    *
    * @param $product_id
    *
    * @return $response - the html product's rich snippet
    *
    */
    public function getProductRichSnippet($merchant,$product_id) {
        
        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $cache = $this->_objectManager->get('Magento\Framework\App\CacheInterface');
        $content = json_decode($cache->load("feedaty_prod_snip".$merchant.$product_id));
        $fdDebugEnabled = $this->_scopeConfig->getValue('feedaty_global/debug/debug_enabled', $store_scope);
        
        if (!$content || strlen($content) < 5 || $content === "null") 
        {
            $path = 'http://white.zoorate.com/gen';
            $dati = [ 'w' => 'wp','MerchantCode' => $merchant,'t' => 'microdata', 'version' => 2, 'sku' => $product_id ];
            $header = [ 'Content-Type: text/html','User-Agent: Mage2' ];
            $dati = $this->serializeData($dati);
            $path.='?'.$dati;
            $ch = curl_init($path);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, '250');
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, '250');
            $content = curl_exec($ch);
            $http_resp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (strlen($content) > 0 && $http_resp == "200") 
            {
                // 6 hours of cache
                $cache->save(json_encode($content), "feedaty_prod_snip".$merchant.$product_id, array("feedaty_cache"), 6*60*60);
            }
            //debug call
            
            if($fdDebugEnabled != 0) {
                $message = "Product microdata response with ".$http_resp." http code";
                $this->feedatyDebug($message, "MICRODATA RESPONSE INFO");
            }
        }

        return $content;
    }
    
    /**
    * Function getMerchantRichSnippet
    *
    * @return $response - the html merchant's rich snippet
    *
    */
    public function getMerchantRichSnippet($merchant){

        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $cache = $this->_objectManager->get('Magento\Framework\App\CacheInterface');
        $content = json_decode($cache->load("feedaty_store_snip".$merchant));
        $fdDebugEnabled = $this->_scopeConfig->getValue('feedaty_global/debug/debug_enabled', $store_scope);

        if (!$content || strlen($content) < 5 || $content === "null") 
        {
            $path = 'http://white.zoorate.com/gen';
            $dati = [
                'w' => 'wp',
                'MerchantCode' => $merchant,
                't' => 'microdata',
                'version' => 2,
            ];
            $header = [
                'Content-Type: text/html',
                'User-Agent: Mage2'
            ];
            $dati = $this->serializeData($dati);
            $path.='?'.$dati;
            $path = str_replace("=2&", "=2", $path);
            $ch = curl_init($path);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, '250');
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, '250');
            $content = curl_exec($ch);
            $http_resp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // 6 hours of cache            
            if (strlen($content) > 0 && $http_resp == "200") 
            {
                $cache->save(json_encode($content), "feedaty_store_snip".$merchant, array("feedaty_cache"), 6*60*60);  
            }
            
            //debug call
            if($fdDebugEnabled != 0) {
                $message = "Merchant microdata response with ".$http_resp." http code";
                $this->feedatyDebug($message, "MICRODATA RESPONSE INFO");
            }
        }
        return $content;
    }

    /**
    * Function _get_FeedatyData
    *
    * @return $data
    */
    public function _get_FeedatyData($feedaty_code) {

        $cache = $this->_objectManager->get('Magento\Framework\App\CacheInterface');
        $content = $cache->load("feedaty_store");

        if (rand(1,3000) === 2000) 
        {
            $this->send_notification($this->_scopeConfig, $this->_storeManager, $this->_dataHelper);
        } 

        $resolver = $this->_objectManager->get('Magento\Framework\Locale\Resolver');

        $string = "FeedatyData".$feedaty_code.$resolver->getLocale();
        $content =$cache->load($string);

        if (!$content || strlen($content) == 0 || $content === "null") 
        {
            $ch = curl_init();
            $url = 'http://widget.stage.zoorate.com/go.php?function=feed_be&action=widget_list&merchant_code='.$feedaty_code.'&language='.$resolver->getLocale();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, '60');
            $content = trim(curl_exec($ch));
            curl_close($ch);

            $cache->save($content, "FeedatyData".$feedaty_code.$resolver->getLocale(), array("feedaty_cache"), 24*60*60); // 24 hours of cache
        }

        $data = json_decode($content, true);
        return $data;
    }

    /**
    * Function send_notification
    *
    * @param object $_scopeConfig
    * @param object $_storeManager
    * @param object Feedaty\Helper\Data dataHelper
    */
    public function send_notification($_scopeConfig, $_storeManager, $feedatyHelper) {

        $store = $_storeManager->getStore();
        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $ver = json_decode(json_encode($feedatyHelper->getExtensionVersion()), true);
        $prodMetadata = $this->_objectManager->get('Magento\Framework\App\ProductMetadataInterface');

        $fdata['keyValuePairs'][] = ["Key" => "Platform", "Value" => "Magento ".$prodMetadata->getVersion()];
        $fdata['keyValuePairs'][] = ["Key" => "Version", "Value" => (string) $feedatyHelper->getExtensionVersion()];
        $fdata['keyValuePairs'][] = ["Key" => "Url", "Value" => $_storeManager->getStore()->getBaseUrl()];
        $fdata['keyValuePairs'][] = ["Key" => "Os", "Value" => PHP_OS];
        $fdata['keyValuePairs'][] = ["Key" => "Php Version", "Value" => phpversion()];
        $fdata['keyValuePairs'][] = ["Key" => "Name", "Value" => $store->getName()];
        $fdata['keyValuePairs'][] = ["Key" => "Action", "Value" => "Enabled"];
        $fdata['keyValuePairs'][] = ["Key" => "Position_Merchant", "Value" => $_scopeConfig->getValue('feedaty_badge_options/widget_store/store_position', $store_scope)];
        $fdata['keyValuePairs'][] = ["Key" => "Position_Product", "Value" => $_scopeConfig->getValue('feedaty_badge_options/widget_products/product_position', $store_scope)];
        $fdata['keyValuePairs'][] = ["Key" => "Status", "Value" => $_scopeConfig->getValue('feedaty_global/sendorder/sendorder', $store_scope)];
        $fdata['merchantCode'] = $_scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', $store_scope);

        $ch = curl_init();

        $url = 'http://www.zoorate.com/ws/feedatyapi.svc/SetPluginKeyValue';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, '60');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($fdata));
        curl_setopt($ch, CURLOPT_HTTPHEADER,['Content-Type: application/json','Expect:']);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $content = trim(curl_exec($ch));

        curl_close($ch);      
    }

    /**
    * Function feedatyDebug() - Save debug infoes in BP/var/log/feedaty.log
    *
    */
    private function feedatyDebug($message, $severity) {
        $fdwriter = new \Zend\Log\Writer\Stream(BP . '/var/log/feedaty.log');
        $fdlogger = new \Zend\Log\Logger();
        $fdlogger->addWriter($fdwriter);
        $fdlogger->info("\n".$severity."\n".$message."\n");
    }
}
// TODO: use magento curl client
