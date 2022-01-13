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

class Orders extends AbstractHelper
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ConfigRules
     */
    protected $helperConfigRules;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var Order
     */
    private $feedatyOrderModel;

    /**
     * @var FeedatyResourceOrder
     */
    private $feedatyOrderResourceModel;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param ConfigRules $helperConfigRules
     * @param ScopeConfigInterface $scopeConfig
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductMetadataInterface $productMetadata
     * @param Order $feedatyOrderModel
     * @param FeedatyResourceOrder $feedatyOrderResourceModel
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        LoggerInterface             $logger,
        OrderRepositoryInterface    $orderRepository,
        ConfigRules                 $helperConfigRules,
        ScopeConfigInterface        $scopeConfig,
        SearchCriteriaBuilder       $searchCriteriaBuilder,
        ProductMetadataInterface    $productMetadata,
        Order                       $feedatyOrderModel,
        FeedatyResourceOrder        $feedatyOrderResourceModel,
        OrderFactory                $orderFactory
    )
    {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->helperConfigRules = $helperConfigRules;
        $this->scopeConfig = $scopeConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productMetadata = $productMetadata;
        $this->feedatyOrderModel = $feedatyOrderModel;
        $this->feedatyOrderResourceModel = $feedatyOrderResourceModel;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @return false|string
     */
    public function getOrderFromDate(){

      //  $fromDate = date('Y-m-d H:i:s', strtotime('-3 hour'));
        $fromDate = date('Y-m-d H:i:s', strtotime('-30 week'));

        return $fromDate;
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

    /**
     * @return array|\Magento\Sales\Api\Data\OrderInterface[]
     */
    public function getOrders()
    {
        $orders = [];
        $status = $this->getOrderstatus();
        $ordersNotified = $this->getFeedatyOrdersNotified();
        if(empty($ordersNotified)){
            $ordersNotified[] = 0;
        }
        try {
            $criteria = $this->searchCriteriaBuilder
                  ->addFilter('status',$status,'eq')
                ->addFilter('entity_id', $ordersNotified, 'nin')
                ->setPageSize(500)
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
     * @return string
     * Get Order Status from Feedaty Configurations or set a default value
     */
    public function getOrderstatus() : string
    {
        $status = 'processing';
        try {
            $status = $this->helperConfigRules->getSendOrderStatus();
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
            $productThumbnail = $item->getProduct()->getImage();
            $productThumbnailUrl = $item->getProduct()->getMediaConfig()->getMediaUrl($productThumbnail);
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

}
