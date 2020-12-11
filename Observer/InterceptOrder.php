<?php
namespace Feedaty\Badge\Observer;

use Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\UrlInterface;
use \Magento\Catalog\Helper\Image;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\Request\Http;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\App\State;
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Customer\Api\CustomerRepositoryInterface;

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
    * @var 
    */
    protected $_customerRepository;


    /**
    * @var 
    */
    protected $_orderRepository;


    /**
    * Constructor
    *
    */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Image $imageHelper,
        WebService $fdservice,
        ObjectManagerInterface $objectmanager,
        State $state,
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository
        ) 
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_imageHelper = $imageHelper;
        $this->_fdservice = $fdservice;
        $this->_objectManager = $objectmanager;
        $this->_state = $state;
        $this->_orderRepository = $orderRepository;
        $this->_customerRepository = $customerRepository;
    }

    /**
    * Function execute
    *
    * @param $observer
    */
    public function execute(\Magento\Framework\Event\Observer $observer) {

        $store = $this->_storeManager->getStore();

        $order_id = $observer->getEvent()->getOrder()->getEntityId();

        if(!$order_id) {$order_id = $order->getRealOrderId();}

        $order = $this->_orderRepository->get( (int)$order_id );

        if( $order !== null ) {

            $feedatyHelper = $this->_objectManager->create('Feedaty\Badge\Helper\Data');

            $merchant = $store->getConfig('feedaty_global/feedaty_preferences/feedaty_code');

            $secret = $store->getConfig('feedaty_global/feedaty_preferences/feedaty_secret');

            $orderopt = $store->getConfig('feedaty_global/feedaty_sendorder/sendorder');

            $fdDebugEnabled = $store->getConfig('feedaty_global/debug/debug_enabled');
        
            //$billingAddress = $order->getBillingAddress()->getCountryId();

            $verify = 0;

            if($fdDebugEnabled != 0) {

                $message = "MerchantCode: ".$merchant." MerchantSecret: ".$secret. "OrderID: " . $order_id;
                
                $feedatyHelper->feedatyDebug($message, "FEEDATY OBSERVER DATA");

            }

            foreach (($order->getAllStatusHistory()) as $orderComment) {

                if ($orderComment->getStatus() === $orderopt) $verify++;

            }

            if ($order->getStatus() == $orderopt && $verify <= 1)  {

                $baseurl_store = $store->getBaseUrl(UrlInterface::URL_TYPE_LINK);

                $objproducts = $order->getAllItems();

                unset($fd_products);

                foreach ($objproducts as $itemId => $item) {

                    unset($tmp);

                    if (!$item->getParentItem()) {

                        //TODO ASSESMENT: Use factories
                        //https://magento.stackexchange.com/questions/91997/magento-2-how-to-retrieve-product-informations/113038#113038

                        $fd_oProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load((int) $item->getProductId());

                        $tmp['SKU'] = $item->getProductId();
                        $tmp['URL'] = $fd_oProduct->getUrlModel()->getUrl($fd_oProduct);
                        $tmp['EAN'] = $item->getEancode();
                    
                        //get the image url
                        if ($fd_oProduct->getImage() != "no_selection") {
                        
                            //$store =  $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
                            $tmp['ThumbnailURL'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $fd_oProduct->getImage();
                        }

                        else {

                            $tmp['ThumbnailURL'] = "";

                        }

                        $tmp['Name'] = $item->getName();
                        $tmp['Brand'] = $item->getBrand();
                        if ($tmp['Brand'] === null) $tmp['Brand']  = "";

                        //$tmp['Price'] = $item->getPrice();
                        $fd_products[] = $tmp;
                    }

                }

                $productMetadata = $this->_objectManager->get('Magento\Framework\App\ProductMetadataInterface');

                // Formatting the array to be sent
                $tmp_order['ID'] = $order->getId();
                $tmp_order['Date'] = date("Y-m-d H:i:s");
                $tmp_order['CustomerEmail'] = $order->getCustomerEmail();
                $tmp_order['CustomerID'] = $order->getCustomerEmail();
                $tmp_order['Platform'] = "Magento ".$productMetadata->getVersion();

                /*if (

                    $billingAddress == 'IT' || 
                    $billingAddress == 'EN' || 
                    $billingAddress == 'ES' || 
                    $billingAddress == 'DE' || 
                    $billingAddress == 'FR' )  
                {

                    $tmp_order['Culture'] = strtolower($billingAddress);

                }

                else*/
                //$tmp_order['Culture'] = 'en';

                $tmp_order['Products'] = $fd_products;

                $fd_data[] = $tmp_order;

                // send to feedaty
                $this->_fdservice->send_order($merchant,$secret,$fd_data);

            }


        }

    }

}
