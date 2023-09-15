<?php
namespace Feedaty\Badge\Model\Config\Source;

use Magento\Backend\Block\System\Store\Edit\Form\Store;
use Magento\Framework\HTTP\Client\CurlFactory;
use Feedaty\Badge\Helper\ConfigRules;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\StoreManagerInterface;

class WebService
{

    /**
     * @var ConfigRules
     */
    protected $_configRules;

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;
    /**
     * @var ModuleListInterface
     */
    private $moduleList;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ConfigRules $configRules
     * @param LoggerInterface $logger
     * @param CurlFactory $curlFactory
     * @param CacheInterface $cache
     * @param Resolver $resolver
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ConfigRules $configRules,
        LoggerInterface $logger,
        CurlFactory $curlFactory,
        CacheInterface $cache,
        Resolver $resolver,
        SerializerInterface $serializer,
        ProductMetadataInterface $productMetadata,
        ModuleListInterface $moduleList,
        StoreManagerInterface $storeManager
        )
    {
        $this->_configRules = $configRules;
        $this->_logger = $logger;
        $this->curlFactory = $curlFactory;
        $this->cache = $cache;
        $this->resolver = $resolver;
        $this->serializer = $serializer;
        $this->productMetadata = $productMetadata;
        $this->moduleList = $moduleList;
        $this->storeManager = $storeManager;
    }


    /*
     * JSON encode
     */
    public function jsonEncode($data)
    {
        return $this->serializer->serialize($data);
    }

    /*
     * JSON decode
     */
    public function jsonDecode($string)
    {
        return $this->serializer->unserialize($string);
    }

    /**
     * @return array
     */
    private function getReqToken()
    {
        $url = "http://api.feedaty.com/OAuth/RequestToken";
        try {
            $curl = $this->curlFactory->create();
            $curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
            $curl->setTimeout(1000);
            $curl->get($url);
            $response = $this->jsonDecode($curl->getBody());
        } catch (\Exception $exception) {
            $response = [];
            $this->_logger->critical('Feedaty | Error cannot get Request Token: '.$exception);
        }

        return $response;
    }


    /**
     * Send Order
     * @param $data
     * @return array|bool|float|int|string|null
     */
    public function sendOrder($data, $storeId, $sendHistory) {

        if($sendHistory === true){
            $url = 'http://api.feedaty.com/Orders/RecordOrdersDataHistory';
        }
        else{
            $url = 'http://api.feedaty.com/Orders/Insert';
        }

        $token = $this->getReqToken();
        $accessToken = $this->getAccessToken($token, $storeId);
        $this->_logger->info('Feedaty | START Cronjob SendOrder');
        try {
            $curl = $this->curlFactory->create();
            $curl->addHeader('Content-Type', 'application/json');
            $curl->addHeader( 'Authorization', 'Oauth '. $accessToken['AccessToken']);
            $curl->setTimeout(1000);
            $curl->post($url,$this->jsonEncode($data));
            $response = $this->jsonDecode($curl->getBody());
        } catch (\Exception $exception) {
            $response = [];
            $this->_logger->critical('Feedaty | Error cannot send order to Feedaty API: '.$exception);
        }

        return $response;

    }


    /**
    * Function getAccessToken - get the access token
    *
    * @param $token
    *
    * @return $response - the access token
    */
    private function getAccessToken($token, $storeId = null) {

        $merchant = $this->_configRules->getFeedatyCode($storeId);
        $secret = $this->_configRules->getFeedatySecret($storeId);

        $encripted_code = $this->encryptToken($token,$merchant,$secret);

        $url = "http://api.feedaty.com/OAuth/AccessToken";
        try {
            $curl = $this->curlFactory->create();
            $curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
            $curl->addHeader( 'Authorization', 'Basic ' . $encripted_code);
            $curl->addHeader( 'User-Agent', 'Mage2');

            $data = [
                'oauth_token' => $token['RequestToken'],
                'grant_type'=>'authorization'
            ];
            $curl->post($url,$data);


            $response = $this->jsonDecode($curl->getBody());

        } catch (\Exception $exception) {
            $response = [];
            $this->_logger->critical('Feedaty | Error cannot get Access Token: '.$exception);
        }

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
        $sha_token = sha1($token['RequestToken'].$secret);
        $base64_sha_token = base64_encode($merchant.":".$sha_token);
        return $base64_sha_token;
    }


    /**
     * @param int $row
     * @param int $count
     * @return mixed|string
     */
    public function getProductReviewsPagination($row, $count, $storeId){
        $allReviews =  $this->getAllReviews('?retrieve=onlyproductreviews&row='.$row.'&count='.$count, $storeId);
        return $allReviews['Reviews'];
    }


    /**
     * @param int $row
     * @param int $count
     * @return mixed
     */
    public function getRemovedReviews($row, $count, $storeId){
        $allReviews =  $this->getAllRemovedReviews('?row='.$row.'&count='.$count, $storeId);
        return $allReviews['Reviews'];
    }

    /**
     * @param int $row
     * @param int $count
     * @return mixed
     */
    public function getMediatedReviews($row, $count, $storeId){
        $allReviews =  $this->getAllMediatedReviews('?row='.$row.'&count='.$count, $storeId);
        return $allReviews['Reviews'];

    }

    /**
     * @return mixed|string
     */
    public function getTotalProductReviewsCount($storeId)
    {
        $allProductReviews = $this->getAllReviews('?retrieve=onlyproductreviews&row=0&count=1', $storeId);
        $totalResults = $allProductReviews['TotalProductReviews'];

        return $totalResults;
    }

    /**
     * Get Removed Reviews Count
     * @return mixed
     */
    public function getTotalProductRemovedReviewsCount($storeId)
    {
        $allProductReviews = $this->getAllRemovedReviews('?row=0&count=1', $storeId);
        $totalResults = $allProductReviews['TotalResults'];

        return $totalResults;
    }

    /**
     * Get Mediated Reviews Count
     * @return mixed
     */
    public function getTotalProductMediatedReviewsCount($storeId)
    {
        $allProductReviews = $this->getAllMediatedReviews('?row=0&count=1', $storeId);
        $totalResults = $allProductReviews['TotalResults'];

        return $totalResults;
    }

    /**
     * Get All Removed Reviews
     * @param string $params
     * @return mixed|null
     */
    public function getAllRemovedReviews($params, $storeId)
    {
        $url = 'http://api.feedaty.com/Reviews/Removed'.$params;
        return $this->getReviewsData($url, $storeId);
    }

    /**
     * Get All Mediated Reviews
     * @param string $params
     * @return mixed|null
     */
    public function getAllMediatedReviews($params, $storeId)
    {
        $url = 'http://api.feedaty.com/Reviews/Mediated'.$params;
        $mediated = $this->getReviewsData($url, $storeId);
        return $mediated;
    }

    /**
     * Get all reviews
     * @param string $params
     * @return mixed|null
     */
    public function getAllReviews($params, $storeId)
    {
        $url = 'http://api.feedaty.com/Reviews/Get'.$params;
         return $this->getReviewsData($url, $storeId);
    }


    /**
     * Get Reviews Data
     * @param $url
     * @return array|mixed|null
     */
    public function getReviewsData($url, $storeId)
    {
        $token = '';
        $token = $this->getReqToken();
        if ($token != '') {
            $accessToken = $this->getAccessToken($token, $storeId);
            $reviews = [];
            try {
                $curl = $this->curlFactory->create();
                $curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
                $curl->addHeader( 'Authorization', 'Oauth '. $accessToken['AccessToken']);
                $curl->setTimeout(1000);
                $curl->get($url);
                $result = $curl->getBody();

                $data = $this->jsonDecode($result);
                $reviews = $data['Data'];

            } catch (\Exception $e) {
                $this->_logger->critical('Feedaty | Error getting Reviews Data: '. $e->getMessage());
            }

            return $reviews;
        }

        return null;
    }


    /**
     * Get Feedaty data
     * @param $feedaty_code
     * @return array|bool|float|int|string|null
     */
    public function getFeedatyData($feedaty_code)
    {

        $string = "FeedatyData" . $feedaty_code . $this->resolver->getLocale();

        $content = $this->cache->load( $string );

        $data = [];

        if ( !$content || strlen($content) == 0 || $content === "null" )
        {
            $url = 'https://widget.feedaty.com/?action=widget_list&style_ver=2021&merchant='.$feedaty_code;

            $arrContextOptions=array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            );

            $content = file_get_contents( $url, false, stream_context_create($arrContextOptions));

            $this->cache->save($content, $string, array("feedaty_cache"), 24*60*60); // 24 hours of cache

        }
        $data = $this->jsonDecode($content);

        return $data;

    }


    /**
     * @return string
     * Get Magento Edition and Version
     */
    public function getPlatform()
    {
        $platform = $this->productMetadata->getName() . ' ' .  $this->productMetadata->getEdition() . ' ' .  $this->productMetadata->getVersion();
        return $platform;
    }


    public function getModuleVersion()
    {
        return $this->moduleList->getOne('Feedaty_Badge')['setup_version'];
    }


    public function getBaseUrl($storeId = null )
    {
        return $this->storeManager->getStore($storeId)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }

    public function getStoreName($storeId = null)
    {
        return $this->storeManager->getStore($storeId)->getName();
    }

    public function fdSendInstallationInfo($storeId)
    {

        /* Platform (obviously Magento) and version */
        $fdata['merchantCode'] = $this->_configRules->getFeedatyCode($storeId);
        $fdata['keyValuePairs'][] = array('Key' => 'Platform', 'Value' => $this->getPlatform());

        /* Plugin version */
        $fdata['keyValuePairs'][] = array('Key' => 'Version', 'Value' => $this->getModuleVersion());

        /* Base store url */
        $fdata['keyValuePairs'][] = array('Key' => 'Url', 'Value' => $this->getBaseUrl());

        /* Php version */
        $fdata['keyValuePairs'][] = array('Key' => 'Php Version', 'Value' => phpversion());

        /* Store name */
        $fdata['keyValuePairs'][] = array('Key' => 'Name', 'Value' => $this->getStoreName($storeId));

        /* Current server date */
        $fdata['keyValuePairs'][] = array('Key' => 'Date', 'Value' => date('c'));

        /* Order Status to Export */
        $fdata['keyValuePairs'][] = array('Key' => 'Status', 'Value' => $this->_configRules->getSendOrderStatus($storeId));

        /* Import Reviews is enable */
        $fdata['keyValuePairs'][] = array('Key' => 'ImportReviews', 'Value' => $this->_configRules->getCreateReviewEnabled($storeId));

        /* Snippet Enabled */
        $fdata['keyValuePairs'][] = array('Key' => 'SnippetEnabled', 'Value' => $this->_configRules->getSnippetEnabled($storeId));

        /* Feedaty Merchant code */
        $fdata['keyValuePairs'][] = array('Key' => 'MerchantCode', 'Value' => $this->_configRules->getFeedatyCode($storeId));

        /* Feedaty Merchant secret */
        $fdata['keyValuePairs'][] = array('Key' => 'MerchantSecret', 'Value' => $this->_configRules->getFeedatySecret($storeId));


        try {
            $url = 'http://www.zoorate.com/ws/feedatyapi.svc/SetPluginKeyValue';
            $curl = $this->curlFactory->create();
            $curl->addHeader('Content-Type', 'application/json');
            $curl->setTimeout(1000);

            $curl->post($url, $this->jsonEncode($fdata));
        } catch (\Exception $e) {
            $this->_logger->critical('Feedaty | Error sending Module Information Data: '. $e->getMessage());
        }

    }
}
