<?php
namespace Feedaty\Badge\Model\Config\Source;

use \Magento\Framework\HTTP\Client\Curl;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\StoreManagerInterface;
use Feedaty\Badge\Helper\Data as FeedatyHelper;
use Feedaty\Badge\Helper\ConfigRules;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\Serialize\Serializer\Json;
use \Psr\Log\LoggerInterface;

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
    protected $feedatyHelper;

    /**
    * @var \Magento\Framework\ObjectManagerInterface
    */
    protected $objectManager;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * @var ConfigRules
     */
    protected $_configRules;

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * WebService constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param FeedatyHelper $feedatyHelper
     * @param Curl $curl
     * @param ObjectManagerInterface $objectmanager
     * @param Json $json
     * @param ConfigRules $configRules
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        FeedatyHelper $feedatyHelper,
        Curl $curl,
        ObjectManagerInterface $objectmanager,
        Json $json,
        ConfigRules $configRules,
        LoggerInterface $logger
        )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_feedatyHelper = $feedatyHelper;
        $this->_curl = $curl;
        $this->_objectManager = $objectmanager;
        $this->_curl->setOption(CURLOPT_FOLLOWLOCATION, 1);
        $this->_curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->_curl->setOption(CURLOPT_VERBOSE, false);
        $this->_json = $json;
        $this->_configRules = $configRules;
        $this->_logger = $logger;

        $timeout = $this->_scopeConfig->getValue(
            'feedaty_global/timeout_connection/timeout',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $timeout = $timeout == null ? "1000" : $timeout;

        $this->_curl->setOption(CURLOPT_CONNECTTIMEOUT_MS, $timeout);
        $this->_curl->setOption(CURLOPT_TIMEOUT_MS, $timeout);
    }


    /**
     * @param $result
     * @return array|bool|float|int|mixed|string|null
     */
    public function unserializeJson($result)
    {
        $jsonDecode = $this->_json->unserialize($result);

        return $jsonDecode;
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
        $this->_logger->info('Feedaty response token //////////////////: '. print_r($response,true));
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

        $timeout = $this->_scopeConfig->getValue(
            'feedaty_global/timeout_widgets/timeout',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $cache_key = "feedaty_product_tab_".$feedaty_code . "PID=" .$id;

        $content = $cache->load($cache_key);

        if (!$content || strlen($content) == 0 || $content === "null")
        {
            $ch = curl_init();

            $resolver = $this->_objectManager->get('Magento\Framework\Locale\Resolver');

            $url = 'http://widget.zoorate.com/go.php?function=feed&action=ws&task=product&merchant_code='.$feedaty_code.'&ProductID='.$id.'&language='.$resolver->getLocale();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            $content = trim(curl_exec($ch));
            curl_close($ch);

            if (strlen($content) > 0)
            {
                // 24 hours of cache
                $cache->save($content, $cache_key, array("feedaty_cache"), 24*60*60);
            }
        }

        $data = json_decode($content, true);

        return $data;
    }

    /**
     * @param $productId
     * @return mixed|string|null
     */
    /*public function getProductReviews($productId){
        return $this->getAllReviews('?retrieve=onlyproductreviews&sku='.$productId);
    }*/

   /* public function getAllProductReviews(){
        return $this->getAllReviews('?retrieve=onlyproductreviews');
    }*/

    public function getProductReviewsPagination($row = 0, $count = 50){
        $allReviews =  $this->getAllReviews('?retrieve=onlyproductreviews&row='.$row.'&count='.$count);
        return $allReviews['Reviews'];
    }


    public function getRemovedReviews($row = 0, $count = 50){
        $allReviews =  $this->getAllRemovedReviews('?row='.$row.'&count='.$count);

        $this->_logger->info('ALL REMOVED REVIEW FEEDATY REMOVED: '. print_r($allReviews['Reviews'],true));

        return $allReviews['Reviews'];

    }

    public function getTotalProductReviewsCount()
    {
        $allProductReviews = $this->getAllReviews('?retrieve=onlyproductreviews&row=0&count=1');

        $totalResults = $allProductReviews['TotalProductReviews'];

        return $totalResults;
    }

    public function getTotalProductRemovedReviewsCount()
    {
        $allProductReviews = $this->getAllRemovedReviews('?row=0&count=1');
        $this->_logger->info('Feedaty REMOVED: '. print_r($allProductReviews,true));
        $totalResults = $allProductReviews['TotalResults'];

        return $totalResults;
    }


    public function getAllRemovedReviews($params = '')
    {
        $url = 'http://api.feedaty.com/Reviews/Removed'.$params;
        return $this->getReviewsData($url);
    }

    public function getAllMediatedReviews($params = '')
    {
        $url = 'http://api.feedaty.com/Reviews/Mediated'.$params;
        $mediated = $this->getReviewsData($url);
        return $mediated;
    }
    /**
     * @param string $params
     * @return mixed|string|null
     * @todo : complete function and use to save data in db
     */
    public function getAllReviews($params = '')
    {
        $url = 'http://api.feedaty.com/Reviews/Get'.$params;
         return $this->getReviewsData($url);
    }



    public function getReviewsData($url)
    {
        $merchant = $this->_configRules->getFeedatyCode();
        $secret = $this->_configRules->getFeedatySecret();

        $token = '';


        try {
            $token = $this->getReqToken();
            if ($token != '') {
                $accessToken =json_decode($this->getAccessToken($token, $merchant, $secret));

                $timeout = $this->_scopeConfig->getValue(
                    'feedaty_global/timeout_connection/timeout',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );

                $this->_curl->addHeader("Content-Type", "application/x-www-form-urlencoded");
                $this->_curl->addHeader("Authorization", 'Oauth '.$accessToken->AccessToken);
                $this->_curl->setTimeout($timeout);
                try {
                    $this->_curl->get($url);
                } catch (\Exception $e) {
                    $this->_logger->critical('Feedaty log CURL: '. $e->getMessage());
                }

                // output of curl request
                $result = $this->_curl->getBody();

                $data = $this->unserializeJson($result);

                $reviews = $data['Data'];

                return $reviews;
            }
        } catch (\Exception $e) {
            $this->_logger->critical('Feedaty log TOKEN: '. $e->getMessage());
        }

        return null;
    }
    /**
    * Function send_order
    *
    * @param object $data
    *
    */
    public function send_order($merchant, $secret, $data) {

        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $timeout = $this->_scopeConfig->getValue(
            'feedaty_global/timeout_orders/timeout',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $ch = curl_init();

        $url = 'http://api.feedaty.com/Orders/Insert';

        $token = $this->getReqToken();

        $accessToken =json_decode($this->getAccessToken($token, $merchant, $secret));

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));

        curl_setopt($ch, CURLOPT_HTTPHEADER,['Content-Type: application/json', 'Authorization: Oauth '.$accessToken->AccessToken]);

        curl_setopt($ch, CURLOPT_HEADER, 1);

        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        $content = trim( curl_exec($ch) );

        $fdDebugEnabled = $this->_scopeConfig->getValue( 'feedaty_global/debug/debug_enabled', $store_scope );

        if($fdDebugEnabled != 0) {

            $message = $data;
            $this->_feedatyHelper->feedatyDebug($message, "FEEDATY DATA");

            $message = $content;
            $this->_feedatyHelper->feedatyDebug($message, "FEEDATY RESPONSE");

            $message  = curl_getinfo($ch,CURLINFO_HEADER_OUT);
            $this->_feedatyHelper->feedatyDebug($message, "CURL HEADER INFO");

            $message  = curl_getinfo($ch,CURLINFO_HEADER_OUT);
            $this->_feedatyHelper->feedatyDebug($message, "CURL INFO");

            if(curl_errno($ch))
                $this->_feedatyHelper->feedatyDebug(curl_error($ch), "CURL ERROR");

        }

        curl_close($ch);

        return 1 ;

    }


    /**
    * Function _get_FeedatyData
    *
    * @return $data
    */
    public function getFeedatyData($feedaty_code) {

        $cache = $this->_objectManager->get('Magento\Framework\App\CacheInterface');

        $resolver = $this->_objectManager->get('Magento\Framework\Locale\Resolver');

        $timeout = $this->_scopeConfig->getValue(
            'feedaty_global/timeout_widgets/timeout',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $string = "FeedatyData" . $feedaty_code . $resolver->getLocale();

        $content = $cache->load( $string );
        if ( !$content || strlen($content) == 0 || $content === "null" )
        {
            $ch = curl_init();

            $url = 'http://widget.zoorate.com/go.php?function=feed_v6&action=widget_list&merchant_code='.$feedaty_code;

            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

            $content = trim(curl_exec($ch));

            curl_close($ch);

            $cache->save($content, $string, array("feedaty_cache"), 24*60*60); // 24 hours of cache

        }

        $data = json_decode($content, true);

        if(!$data){
            $data = [];
        }
        return $data;

    }


    public function getRatings( $merchant, $sku = "null" , $scope = "product" ) {

        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $fdDebugEnabled = $this->_scopeConfig->getValue('feedaty_global/debug/debug_enabled', $store_scope);

        $timeout = $this->_scopeConfig->getValue('feedaty_global/timeout_microdata/timeout', $store_scope );

        $cache_interface = $this->_objectManager->get('Magento\Framework\App\CacheInterface');

        $cache_key = "feedaty_ratings_". $merchant . "_" . $sku;

        $content = json_decode( $cache_interface->load( $cache_key ), true);

        if ( !$content['data'] || $content['data'] == null )
        {

            $url = 'https://widget.zoorate.com/go.php?function=feed_v6&action=ratings'.
            '&scope=' . $scope .
            '&sku=' . $sku .
            '&merchant=' . $merchant .
            '&lang=null' ;

            $header = [ 'Content-Type: text/html','User-Agent: Mage2' ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);

            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $timeout);

            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);

            $content = json_decode( curl_exec($ch), true );

            $http_resp = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($content != null && $http_resp == "200")
            {
                // 6 hours of cache
                $cache_interface->save(

                    json_encode($content ,true),
                    $cache_key,
                    array("feedaty_cache"),
                    24*60*60

                );
            }
            //debug call

            if($fdDebugEnabled != 0) {

                $message = "Rating API response:  ".$http_resp." http code";
                $this->_feedatyHelper->feedatyDebug($message, "API RATINGS RESPONSE INFO");

            }
        }

        return $content['data'];

    }

}
/* TODO:

-Implement magento curl client
-Implement new API for product page reviews list
-Implement Carousel Section
-Implement Popup Section

*/
