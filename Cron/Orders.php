<?php

namespace Feedaty\Badge\Cron;

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

    /**
     * @param LoggerInterface $logger
     * @param OrdersHelper $ordersHelper
     */
    public function __construct(
        LoggerInterface         $logger,
        OrdersHelper            $ordersHelper,
        WebService $webService
    )
    {
        $this->logger = $logger;
        $this->ordersHelper = $ordersHelper;
        $this->webService = $webService;
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

        $orders = $this->ordersHelper->getOrders();


         $data = array();

        /* Order Increment */
        $i = 0;

        if(count($orders) > 0){
            foreach ($orders as $order){

                /* Get all visible order products */
                $items = $order->getAllVisibleItems();

                $storeId =  $order->getStoreId();
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

                    $productId = $item->getProductId();

                    $productThumbnailUrl = $this->ordersHelper->getProductThumbnailUrl($item);

                    if ($item->getParentItem()) {
                        $product = $item->getParentItem()->getProduct();
                    } else {
                        $product = $item->getProduct();
                    }
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

                $this->ordersHelper->setFeedatyCustomerNotified($order->getEntityId());
                $i++;
            }

            $response = (array) $this->webService->sendOrder($data);

            if(!empty($response)){
                if(isset($response['Data'])){
                    foreach ($response['Data'] as $data){
                        //if order Success or Duplicated set Feedaty Customer Notification true
                        if($data['Status'] == '1' || $data['Status'] == '201'){
                            $this->logger->info("Feedaty | Order sent successfull: order ID " . $order->getEntityId() . ' - date: ' . date('Y-m-d H:i:s') );
                        }
                        else {
                            $this->logger->critical("Feedaty | Order not sent: order ID  " .$order->getEntityId() . ' - date: '  . date('Y-m-d H:i:s') );
                        }
                    }
                }
            }
        }

    }

}

