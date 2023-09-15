<?php

declare(strict_types=1);

namespace Feedaty\Badge\Helper;

use Feedaty\Badge\Model\Order;
use Feedaty\Badge\Model\OrderFactory;
use Feedaty\Badge\Model\ResourceModel\Order as FeedatyResourceOrder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use Feedaty\Badge\Model\Config\Source\WebService;

class Orders extends AbstractHelper
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var ConfigRules
     */
    protected ConfigRules $helperConfigRules;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var ProductMetadataInterface
     */
    protected ProductMetadataInterface $productMetadata;

    /**
     * @var FeedatyResourceOrder
     */
    private FeedatyResourceOrder $feedatyOrderResourceModel;

    /**
     * @var OrderFactory
     */
    private OrderFactory $orderFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var WebService
     */
    private WebService $webService;

    /**
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param ConfigRules $helperConfigRules
     * @param ScopeConfigInterface $scopeConfig
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductMetadataInterface $productMetadata
     * @param FeedatyResourceOrder $feedatyOrderResourceModel
     * @param OrderFactory $orderFactory
     * @param StoreManagerInterface $storeManager
     * @param WebService $webService
     */
    public function __construct(
        LoggerInterface             $logger,
        OrderRepositoryInterface    $orderRepository,
        ConfigRules                 $helperConfigRules,
        ScopeConfigInterface        $scopeConfig,
        SearchCriteriaBuilder       $searchCriteriaBuilder,
        ProductMetadataInterface    $productMetadata,
        FeedatyResourceOrder        $feedatyOrderResourceModel,
        OrderFactory                $orderFactory,
        StoreManagerInterface       $storeManager,
        WebService $webService
    )
    {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->helperConfigRules = $helperConfigRules;
        $this->scopeConfig = $scopeConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productMetadata = $productMetadata;
        $this->feedatyOrderResourceModel = $feedatyOrderResourceModel;
        $this->orderFactory = $orderFactory;
        $this->storeManager = $storeManager;
        $this->webService = $webService;
    }


    /**
     * @param $storeId
     * @return string
     */
    public function getCulture($storeId) : string
    {
        $locale = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $culture = strtok($locale, '_');
        $allowedLanguages = array("it", "en", "es", "fr", "de");

        //Set default language fallback
        if (!in_array($culture, $allowedLanguages)) {
            $this->logger->warning('Feedaty | Culture not found | Set default language fallback for '. $culture);
            $culture = "en";
        }

        return $culture;
    }

    /*
     * get Product EAN
     */
    public function getProductEan($storeId, $item) : string
    {

        $ean = '';

        $enableEan = $this->helperConfigRules->getSendOrderEnableEan($storeId);

        if($enableEan === '1'){

            $eanCode = $this->helperConfigRules->getSendOrderEan($storeId);

            $childrenItems = $item->getChildrenItems();

            if (!empty($childrenItems)) {
                $count = 0;
                foreach($childrenItems as $child){
                    if($count === 0){
                        $ean = $child->getProduct()->getData($eanCode) ? $child->getProduct()->getData($eanCode) :  '';
                    }
                    $count++;
                }
            }
            else {
                $ean = $item->getProduct()->getData($eanCode) ? $item->getProduct()->getData($eanCode) :  '';
            }
        }

        return $ean;
    }

    /**
     * @param $storeId
     * @return array
     */
    public function getOrders($storeId): array
    {
        $orders = [];
        $status = $this->getOrderstatus($storeId);

        $ordersNotified = $this->getFeedatyOrdersNotified();
        if(empty($ordersNotified)){
            $ordersNotified[] = 0;
        }
        try {
            $to = date("Y-m-d h:i:s"); // current date
            $range = strtotime('-24 hours', strtotime($to));
            $from = date('Y-m-d h:i:s', $range); // 24 hours before

            $criteria = $this->searchCriteriaBuilder
                ->addFilter('updated_at', $from, 'gteq')
                ->addFilter('entity_id', $ordersNotified, 'nin')
                ->addFilter('store_id', $storeId,'eq')
                ->addFilter('status', $status,'eq')
                ->setPageSize(50)
                ->setCurrentPage(1)
                ->create();

            $orderResult = $this->orderRepository->getList($criteria);

            $orders = $orderResult->getItems();

        } catch (\Exception $e) {
            $this->logger->critical('Feedaty | Error - Cannot get orders - '. $e->getMessage());
        }

        return $orders;
    }

    /**
     * @param $orders
     * @param $storeId
     * @param $sendHistory
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendFeedatyOrders($orders, $storeId, $sendHistory): void
    {
        foreach ($orders as $order){

            /**
             * Get all visible order products
             */
            $items = $order->getAllVisibleItems();

            /**
             * Get Locale
             */
            $localeCode = $this->getCulture($storeId);

            /**
             * Set Order Data
             */
            $data[$i] = [
                'ID' => $order->getEntityId(),
                'Date' => $order->getCreatedAt(),
                'CustomerEmail' => $order->getCustomerEmail(),
                'CustomerID' => $order->getCustomerEmail(),
                'Culture' => $localeCode,
                'Platform' => $this->getPlatform(),
                'Products' => []
            ];

            /**
             * If Send History is true set order status
             */
            if ($sendHistory === true) {
                $data[$i]['Status'] = $order->getStatus();
            }

            /**
             * Get Product Data
             */
            foreach ($items as $item){
                /**
                 * Get Product Thumbnail
                 */
                $productThumbnailUrl = $this->getProductThumbnailUrl($item);

                $product = $item->getProduct();

                if ($product) {

                    /**
                     * Get Product ID
                     */
                    $productId = $product->getId();

                    /**
                     * Get Product Url
                     */
                    $productUrl = '';
                    if ($item->getProductType() === 'grouped'){
                        $options = $item->getProductOptions();
                        if(!empty($options['info_buyRequest'])) {
                            if(!empty($options['super_product_config']["product_id"])) {
                                $productUrl = $this->storeManager->getStore($storeId)->getBaseUrl() . 'catalog/product/view/id/'.$options['super_product_config']["product_id"].'/?___store='.$storeId;
                            }
                        }
                    }
                    else{
                        $productUrl = $this->storeManager->getStore($storeId)->getBaseUrl() . 'catalog/product/view/id/'.$productId.'/?___store='.$storeId;
                    }

                    /**
                     * Get Product EAN
                     */
                    $ean = $this->getProductEan($storeId, $item);

                    /**
                     * Set Product Data
                     */
                    $data[$i]['Products'][] = [
                        'SKU' => $productId,
                        'URL' => $productUrl,
                        'ThumbnailURL' => $productThumbnailUrl,
                        'Name' => $item->getName(),
                        'EAN' => $ean
                    ];
                }
            }

            /**
             * Set Order As Sent on Magento
             */
            if ($sendHistory === true) {
                $this->setFeedatyHistorySaved($order->getEntityId());
            }
            else{
                $this->setFeedatyCustomerNotified($order->getEntityId());
            }

            $i++;
        }


        if ($sendHistory === true) {
            /**
             * Send Order to Feedaty History API
             */
            $response = (array) $this->webService->sendOrder($data, $storeId, true);
        }
        else {
            /**
             * Send Order to Feedaty Orders API
             */
            $response = (array) $this->webService->sendOrder($data, $storeId, false);
        }


        if(!empty($response)){
            if(isset($response['Data'])){
                foreach ($response['Data'] as $dataResponse){
                    //if order Success or Duplicated set Feedaty Customer Notification true
                    if($dataResponse['Status'] == '1' || $dataResponse['Status'] == '201'){
                        $this->_logger->info("Feedaty | Order sent successfull: order ID " . $order->getEntityId() . ' - date: ' . date('Y-m-d H:i:s') . ' SendHistoryOrder ' . $sendHistory );
                    }
                    else {
                        $this->_logger->critical("Feedaty | Order not sent: order ID  " . $order->getEntityId() . ' - date: '  . date('Y-m-d H:i:s') . ' SendHistoryOrder ' . $sendHistory);
                    }
                }
            }
            else {
                $this->_logger->critical("Feedaty | No Data Response" . print_r($response,true) . ' SendHistoryOrder ' . $sendHistory);
            }
        }
        else {
            $this->_logger->critical("Feedaty | Empty Response". ' SendHistoryOrder ' . $sendHistory);
        }
    }

    public function getHistoryOrders($storeId): array
    {
        $orders = [];

        $ordersNotified = $this->getFeedatyOrdersHistorySaved();
        if(empty($ordersNotified)){
            $ordersNotified[] = 0;
        }
        try {
            $to = date("Y-m-d h:i:s"); // current date
            //todo set to 24 h
            $range = strtotime('-2400 hours', strtotime($to));
            $from = date('Y-m-d h:i:s', $range); // 24 hours before

            $criteria = $this->searchCriteriaBuilder
                ->addFilter('updated_at', $from, 'gteq')
                ->addFilter('entity_id', $ordersNotified, 'nin')
                ->addFilter('store_id', $storeId,'eq')
                ->addFilter('status', ['canceled','fraud','holded'],'nin')
                ->setPageSize(50)
                ->setCurrentPage(1)
                ->create();

            $orderResult = $this->orderRepository->getList($criteria);

            $orders = $orderResult->getItems();

        } catch (\Exception $e) {
            $this->logger->critical('Feedaty | Error - Cannot get orders - '. $e->getMessage());
        }

        return $orders;
    }


    /**
     * Get Orders for Cron Api
     * @return array|\Magento\Sales\Api\Data\OrderInterface[]
     */
    public function getCsvOrders($from, $to, $storeId)
    {
        $orders = [];
        $status = $this->getOrderstatus($storeId);
        try {
            $criteria = $this->searchCriteriaBuilder
                ->addFilter('created_at', $from, 'gteq')
                ->addFilter('created_at', $to, 'lteq')
                ->addFilter('store_id', $storeId,'eq')
                ->addFilter('status', $status,'eq')
                ->create();

            $orderResult = $this->orderRepository->getList($criteria);

            $orders = $orderResult->getItems();

        } catch (\Exception $e) {
            $this->logger->critical('Feedaty | Error - Cannot get orders - '. $e->getMessage());
        }

        return $orders;

    }

    /**
     * @return string
     * Get Order Status from Feedaty Configurations or set a default value
     */
    public function getOrderstatus($storeId) : string
    {
        $status = 'processing';
        try {
            $status = $this->helperConfigRules->getSendOrderStatus($storeId);
        } catch (\Exception $e) {
            $this->logger->critical('Feedaty | Error - Cannot find send order status configuration. Set order status complete as default value - '. $e->getMessage());
        }

        return $status;
    }

    public function getFeedatyOrdersNotified()
    {
        $ordersNotified = [];
        $orders = $this->orderFactory->create()->getCollection();

        foreach($orders as $order){
            $ordersNotified[] = $order->getOrderId();
        }
        return $ordersNotified;
    }

    /**
     * Get Feedaty Orders History Saved
     * @return array
     */
    public function getFeedatyOrdersHistorySaved()
    {
        $ordersHistorySaved = [];
        $orders = $this->orderFactory->create()
            ->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'feedaty_history_saved',
                1
            );

        foreach($orders as $order){
            $ordersHistorySaved[] = $order->getOrderId();
        }

        return $ordersHistorySaved;
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

    /**
     * @param $item
     * @return string
     * Get Product Image URL
     */
    public function getProductThumbnailUrl($item) : string
    {
        $productThumbnailUrl = '';
        try {
            if ($item->getParentItem()) {
                $product = $item->getParentItem()->getProduct();
            } else {
                $product = $item->getProduct();
            }
            if($product){
                $productThumbnail = $product->getImage();
                $productThumbnailUrl = $product->getMediaConfig()->getMediaUrl($productThumbnail);
            }

        } catch (\Exception $e) {
            $this->logger->critical('Feedaty | Error - Cannot get product thumbnail URL - '. $e->getMessage());
        }

        return $productThumbnailUrl;
    }

    /*
     * Get Feedaty Customer Notified Orders
     */

    /**
     * Set Feedaty Customer Notified Orders
     * @param $order
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function setFeedatyCustomerNotified($orderId)
    {
        $feedatyOrder = $this->orderFactory->create();

        $feedatyOrder->setOrderId($orderId);
        $feedatyOrder->setFeedatyCustomerNotified(1);

        $this->feedatyOrderResourceModel->save($feedatyOrder);

    }

    public function setFeedatyHistorySaved($orderId)
    {
        $feedatyOrder = $this->orderFactory->create();

        $feedatyOrder->setOrderId($orderId);
        $feedatyOrder->setFeedatyHistorySaved(1);

        $this->feedatyOrderResourceModel->save($feedatyOrder);

    }

}
