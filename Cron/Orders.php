<?php

namespace Feedaty\Badge\Cron;

use Feedaty\Badge\Helper\Data;
use Feedaty\Badge\Helper\Orders as OrdersHelper;
use Feedaty\Badge\Model\Config\Source\WebService;
use Magento\Framework\Url;
use Psr\Log\LoggerInterface;
use Feedaty\Badge\Helper\ConfigRules;

class Orders
{

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var OrdersHelper
     */
    protected $ordersHelper;
    /**
     * @var WebService
     */
    private $webService;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var ConfigRules
     */
    protected $_configRules;

    /**
     * @var Url
     */
    private $url;

    /**
     * @param LoggerInterface $logger
     * @param OrdersHelper $ordersHelper
     * @param WebService $webService
     * @param Data $dataHelper
     * @param ConfigRules $configRules
     */
    public function __construct(
        LoggerInterface         $logger,
        OrdersHelper            $ordersHelper,
        WebService $webService,
        Data $dataHelper,
        ConfigRules $configRules,
        Url $url
    )
    {
        $this->_logger = $logger;
        $this->ordersHelper = $ordersHelper;
        $this->webService = $webService;
        $this->dataHelper = $dataHelper;
        $this->_configRules = $configRules;
        $this->url = $url;
    }

    /**
     * Send Orders to Feedaty Orders API
     */
    public function execute()
    {

        //Starter Log
        $this->_logger->info("Feedaty | START Cronjob | Set Feedaty Orders  | date: " . date('Y-m-d H:i:s') );

        /**
         * Get stores
         */
        $storesIds = $this->dataHelper->getAllStoresIds();

        foreach ($storesIds as $storeId) {

            /**
             * Send Module Information Data
             */
            $this->webService->fdSendInstallationInfo($storeId);


            /**
             * Get Orders
             */
            $orders = $this->ordersHelper->getOrders($storeId);

            $data = [];

            /* Order Increment */
            $i = 0;

            $debugMode = $this->_configRules->getDebugModeEnabled($storeId);

            if($debugMode === "1") {
                $this->_logger->info("Feedaty Debug Mode | Get Orders Data | " . count($orders) . " date: ".  date('Y-m-d H:i:s') );
            }

            if(count($orders) > 0){
                foreach ($orders as $order){

                    /* Get all visible order products */
                    $items = $order->getAllVisibleItems();

                    /**
                     * Get Locale
                     */
                    $localeCode = $this->ordersHelper->getCulture($storeId);

                    $data[$i] = [
                        'ID' => $order->getEntityId(),
                        'Date' => $order->getCreatedAt(),
                        'CustomerEmail' => $order->getCustomerEmail(),
                        'CustomerID' => $order->getCustomerEmail(),
                        'Culture' => $localeCode,
                        'Platform' => $this->ordersHelper->getPlatform(),
                        'Products' => []
                    ];

                    foreach ($items as $item){

                        /**
                         * Get Product Id
                         */
                        $productId = $item->getProductId();

                        /**
                         * Get Product Thumbnail
                         */
                        $productThumbnailUrl = $this->ordersHelper->getProductThumbnailUrl($item);

                        if ($item->getParentItem()) {
                            $product = $item->getParentItem()->getProduct();
                        } else {
                            $product = $item->getProduct();
                        }

                        /*
                         * Get Product Url
                         */
                        $productUrl = '';
                        if($product){
                            $productUrl =  $this->url->getUrl('catalog/product/view', ['id' => $productId, '_nosid' => true, '_query' => ['___store' => $storeId]]);
                        }

                        $ean = $this->ordersHelper->getProductEan($storeId, $item);
                        array_push($data[$i]['Products'],
                            [
                                'SKU' => $productId ,
                                'URL' => $productUrl,
                                'ThumbnailURL' => $productThumbnailUrl,
                                'Name' => $item->getName(),
                                'EAN' => $ean
                            ]
                        );
                    }

                    /**
                     * Set Order As Sent
                     */
                    $this->ordersHelper->setFeedatyCustomerNotified($order->getEntityId());

                    $i++;
                }

                $response = (array) $this->webService->sendOrder($data, $storeId);

                if(!empty($response)){
                    if(isset($response['Data'])){
                        foreach ($response['Data'] as $dataResponse){
                            //if order Success or Duplicated set Feedaty Customer Notification true
                            if($dataResponse['Status'] == '1' || $dataResponse['Status'] == '201'){
                                $this->_logger->info("Feedaty | Order sent successfull: order ID " . $order->getEntityId() . ' - date: ' . date('Y-m-d H:i:s') );
                            }
                            else {
                                $this->_logger->critical("Feedaty | Order not sent: order ID  " . $order->getEntityId() . ' - date: '  . date('Y-m-d H:i:s') );
                            }
                        }
                    }
                    else {
                        $this->_logger->critical("Feedaty | No Data Response" . print_r($response,true));
                    }
                }
                else {
                    $this->_logger->critical("Feedaty | Empty Response" );
                }
            }
        }

        //SKIP Log
        $this->_logger->info("Feedaty | SKIP Cronjob | No orders to import  | date: " . date('Y-m-d H:i:s') );

    }

}

