<?php

namespace Feedaty\Badge\Observer;

use Feedaty\Badge\Model\Config\Source\WebService;

use Feedaty\Badge\Helper\Data as FeedatyHelper;

use \Magento\Framework\Event\ObserverInterface;

use \Magento\Framework\UrlInterface;

use \Magento\Catalog\Helper\Image;

use \Magento\Store\Model\StoreManagerInterface;

use \Magento\Store\Api\Data\StoreConfigInterface;

use \Magento\Framework\App\Config\ScopeConfigInterface;

use \Magento\Framework\App\Request\Http;

use \Magento\Framework\ObjectManagerInterface;

use \Magento\Framework\App\State;

use \Magento\Sales\Api\Data\OrderInterface;


class InterceptOrder implements ObserverInterface
{

    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $_scopeConfig;

    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $_storeManager;

    /**
    * @var \Magento\Store\Api\Data\StoreInterface
    */
    protected $_storeConfigInterface;

    /**
    * @var \Magento\Catalog\Helper\Image
    */
    protected $_imageHelper;

    /**
    * @var \Magento\Framework\ObjectManagerInterface
    */
    protected $_objectManager;

    /**
    * @var \Magento\Framework\App\State
    */
    protected $_state;

    /**
    * @var Feedaty\Badge\Helper\Data
    */
    protected $feedatyHelper;


    /**
    * @var
    */
    protected $_orderInterface;


    /**
    * Constructor
    *
    */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        StoreConfigInterface $storeConfigInterface,
        Image $imageHelper,
        WebService $fdservice,
        ObjectManagerInterface $objectmanager,
        State $state,
        OrderInterface $orderInterface,
        FeedatyHelper $feedatyHelper
        )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_storeConfigInterface = $storeConfigInterface;
        $this->_imageHelper = $imageHelper;
        $this->_fdservice = $fdservice;
        $this->_objectManager = $objectmanager;
        $this->_state = $state;
        $this->_orderInterface = $orderInterface;
        $this->_feedatyHelper = $feedatyHelper;
    }

    /**
    * Function execute
    *
    * @param $observer
    */
    public function execute( \Magento\Framework\Event\Observer $observer ) {

        $increment_id = $observer->getEvent()->getOrder()->getIncrementId();

        try {

            $order = $this->_orderInterface->loadByIncrementId( $increment_id );

            $store_id = $order->getStoreId();

            $store = $store_id == null ? $this->_storeManager->getStore() : $this->_storeManager->getStore($store_id);


            $culture_code = $store->getConfig('general/locale/code');



            $orderopt = $store->getConfig('feedaty_global/feedaty_sendorder/sendorder');

            if( $order !== null  ) {

                $order_id = $order->getId();

                $merchant = $store->getConfig('feedaty_global/feedaty_preferences/feedaty_code');

                $secret = $store->getConfig('feedaty_global/feedaty_preferences/feedaty_secret');

                $fdDebugEnabled = $store->getConfig('feedaty_global/debug/debug_enabled');



                $verify = 0;

                if($fdDebugEnabled != 0) {

                    $message = "MerchantCode: ".$merchant." MerchantSecret: ".$secret. "OrderID: " . $order_id;

                    $this->_feedatyHelper->feedatyDebug(
                        $message,
                        "FEEDATY OBSERVER DATA"
                    );

                }

                foreach ( ($order->getAllStatusHistory() ) as $orderComment ) {

                    if ( $orderComment->getStatus() === $orderopt )

                        $verify++;

                }

                if ( $verify <= 1 )  {

                    $baseurl_store = $store->getBaseUrl( UrlInterface::URL_TYPE_LINK );

                    // errore
                    $objproducts = $order->getAllItems() ;

                    $objproducts = empty($objproducts) ? $order->getItems() : $objproducts ;

                    if( !empty($objproducts) ) {

                        unset($fd_products);

                        foreach ( $objproducts as $itemId => $item ) {

                            unset($tmp);

                            if ( !$item->getParentItem() ) {

                                //TODO ASSESMENT: Use factories
                                //https://magento.stackexchange.com/questions/91997/magento-2-how-to-retrieve-product-informations/113038#113038

                                $orderProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load((int) $item->getProductId());

                                $tmp['SKU'] = $item->getProductId();

                                $tmp['URL'] = $orderProduct->getUrlModel()->getUrl($orderProduct);

                                //$tmp['EAN'] = $item->getCustomAttribute('ean');

                                //get the image url
                                if ( $orderProduct->getImage() != "no_selection" ) {

                                    $tmp['ThumbnailURL'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $orderProduct->getImage();
                                }

                                else {

                                    $tmp['ThumbnailURL'] = "";

                                }

                                $tmp['Name'] = $item->getName();

                                $tmp['Brand'] = $item->getBrand();

                                if ($tmp['Brand'] === null) $tmp['Brand']  = "";

                                $fd_products[] = $tmp;

                            }

                            //configurable and bundle products
                            else {

                                $parentProductID = $item->getParentItem()->getProductId();

                                $childProductID = $item->getProductId();

                                $parentProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')
                                    ->load( (int) $parentProductID );

                                $childProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')
                                    ->load( (int) $childProductID );


                                $tmp['SKU'] = $parentProductID;

                                $tmp['URL'] = $parentProduct->getUrlModel()->getUrl($parentProduct);

                                //$tmp['EAN'] = $childProduct->getCustomAttribute('ean');

                                //get the image url
                                if ($childProduct->getImage() != "no_selection") {

                                    $tmp['ThumbnailURL'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $childProduct->getImage();
                                }

                                else {

                                    $tmp['ThumbnailURL'] = "";

                                }

                                $tmp['Name'] = $parentProduct->getName();

                                $tmp['Brand'] = $parentProduct->getBrand();

                                if ($tmp['Brand'] === null) $tmp['Brand']  = "";

                                $this->_feedatyHelper->feedatyDebug(

                                    json_encode($tmp),
                                    "FEEDATY configurable Product: "

                                );

                                $fd_products[] = $tmp;

                            }

                        }

                        $cultures = explode( "_", $culture_code );

                        $culture = $cultures[0];

                        $allowedLanguages = array("it", "en", "es", "fr","de");
                        if (!in_array($culture, $allowedLanguages)) {
                            $culture = 'en';
                        }

                        $mageMetadata = $this->_objectManager->get('Magento\Framework\App\ProductMetadataInterface');

                        // Formatting the array to be sent
                        $tmp_order['ID'] = $order->getId();

                        $tmp_order['Date'] = date("Y-m-d H:i:s");

                        $tmp_order['CustomerEmail'] = $order->getCustomerEmail();

                        $tmp_order['CustomerID'] = $order->getCustomerEmail();

                        $tmp_order['Culture'] = $culture;

                        $tmp_order['Platform'] = "Magento ".$mageMetadata->getVersion();

                        $tmp_order['Products'] = $fd_products;

                        $fd_data[] = $tmp_order;

                        // send to feedaty

                        echo "<pre>";
                        var_dump($fd_data);
                        echo "</pre>";


                        print_r($merchant);
                        print_r($secret);
                        die('FEEDATY ORDER DATA: DATA');

                        $this->_fdservice->send_order( $merchant, $secret, $fd_data );

                    }

                    else {

                        $this->_feedatyHelper->feedatyDebug( "Can't find order products", "FEEDATY: " );

                    }

                }

            }

        }

        catch (Exception $exception) {

            $this->_feedatyHelper->feedatyDebug( $exception->getMessage, "FEEDATY: " );

        }


    }

}
