<?php

namespace Feedaty\Badge\Cron;

use Feedaty\Badge\Helper\Data;
use Feedaty\Badge\Helper\Orders as OrdersHelper;
use Feedaty\Badge\Model\Config\Source\WebService;
use Psr\Log\LoggerInterface;

class Orders
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OrdersHelper
     */
    protected $ordersHelper;
    /**
     * @var WebService
     */
    private $webService;

    protected $dataHelper;

    /**
     * @param LoggerInterface $logger
     * @param OrdersHelper $ordersHelper
     * @param WebService $webService
     * @param Data $dataHelper
     */
    public function __construct(
        LoggerInterface         $logger,
        OrdersHelper            $ordersHelper,
        WebService $webService,
        Data $dataHelper
    )
    {
        $this->logger = $logger;
        $this->ordersHelper = $ordersHelper;
        $this->webService = $webService;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Send Orders to Feedaty Orders API
     */
    public function execute()
    {
        /**
         * Send Module Information Data
         */
        $this->webService->fdSendInstallationInfo();

        //Starter Log
        $this->logger->info("Feedaty | START Cronjob | Set Feedaty Orders  | date: " . date('Y-m-d H:i:s') );

        /**
         * TODO : create store view ids foreach - Get Stores
         */

        $storesIds = $this->dataHelper->getAllStoresIds();

        foreach ($storesIds as $storeId) {

            /**
             * Get Orders
             */
            $orders = $this->ordersHelper->getOrders($storeId);

            $data = [];

            /* Order Increment */
            $i = 0;

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
                            $productUrl = $product->getProductUrl();
                        }

                        array_push($data[$i]['Products'],
                            [
                                'SKU' => $productId ,
                                'URL' => $productUrl,
                                'ThumbnailURL' => $productThumbnailUrl,
                                'Name' => $item->getName()
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
                                $this->logger->info("Feedaty | Order sent successfull: order ID " . $order->getEntityId() . ' - date: ' . date('Y-m-d H:i:s') );
                            }
                            else {
                                $this->logger->critical("Feedaty | Order not sent: order ID  " . $order->getEntityId() . ' - date: '  . date('Y-m-d H:i:s') );
                            }
                        }
                    }
                    else {
                        $this->logger->critical("Feedaty | No Data Response" . print_r($response,true));
                    }
                }
                else {
                    $this->logger->critical("Feedaty | Empty Response" );
                }
            }
        }
    }
}

