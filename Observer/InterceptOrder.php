<?php
namespace Feedaty\Badge\Observer;

use Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\UrlInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Feedaty\Badge\Helper\Data as DataHelp;
use \Magento\Framework\App\ObjectManager;

class InterceptOrder implements ObserverInterface
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
    protected $dataHelpler;

    /**
    * @var Feedaty\Badge\Model\Config\Source\WebService
    */
    protected $fdservice;

    /**
    * @var \Magento\Framework\App\ObjectManager
    */
    protected $objectManager;

    /**
    * Constructor
    *
    */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DataHelp $dataHelpler,
        WebService $fdservice,
        ObjectManager $objectManager
        ) 
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_dataHelpler = $dataHelpler;
        $this->_fdservice = $fdservice;
        $this->_objectManager = $objectManager;
    }

    /**
    * Function execute
    *
    * @param $observer
    */
    public function execute(\Magento\Framework\Event\Observer $observer){

        $order = $observer->getEvent()->getOrder();
        $order_id = $order->getIncrementId();
        $billingAddress = $order->getBillingAddress()->getCountryId();
        $verify = 0;
        $store = $this->_storeManager->getStore($order->getStore_id());

        $merchant = $store->getConfig('feedaty_global/feedaty_preferences/feedaty_code');
        $secret = $store->getConfig('feedaty_global/feedaty_preferences/feedaty_secret');
        $orderopt = $store->getConfig('feedaty_global/feedaty_sendorder/sendorder');

        foreach (($order->getAllStatusHistory()) as $orderComment) 
        {
            if ($orderComment->getStatus() === $orderopt) $verify++;
        }

        if ($order->getStatus() == $orderopt && $verify <= 1) 
        {

            $baseurl_store = $store->getBaseUrl(UrlInterface::URL_TYPE_LINK);

            $objproducts = $order->getAllItems();

            unset($fd_products);

            foreach ($objproducts as $itemId => $item) 
            {
                unset($tmp);

                if (!$item->getParentItem()) 
                {
                    $fd_oProduct = $this->_objectManager->get('Magento\Catalog\Model\Product')->load((int) $item->getProductId());

                    $tmp['SKU'] = $item->getProductId();
                    $tmp['URL'] = $fd_oProduct->getUrlModel()->getUrl($fd_oProduct);

                        //get the image url
                    if ($fd_oProduct->getImage() != "no_selection") 
                    {
                        $tmp['ThumbnailURL'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $fd_oProduct->getImage();
                    }
                    else 
                    {
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

            if ($billingAddress == 'IT' || $billingAddress == 'EN' || $billingAddress == 'ES' || $billingAddress == 'DE' || $billingAddress == 'FR') 
            {
                $tmp_order['Culture'] = strtolower($billingAddress);
            }
            else $tmp_order['Culture'] = 'en';

            $tmp_order['Products'] = $fd_products;
            $fd_data[] = $tmp_order;

            // send to feedaty

            $this->_fdservice->send_order($merchant,$secret,$fd_data);

        }
    }
}

