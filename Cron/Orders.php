<?php

namespace Feedaty\Badge\Cron;

use Feedaty\Badge\Helper\ConfigRules;
use Feedaty\Badge\Helper\Reviews as ReviewsHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Orders
{


    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * @var \Feedaty\Badge\Model\Config\Source\WebService
     */
    protected $_webService;
    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;
    /**
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $_ratingFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var ConfigRules
     */
    protected $_configRules;

    /**
     * @var ReviewsHelper
     */
    protected $ordersHelper;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;


    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Feedaty\Badge\Model\Config\Source\WebService $webService
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param ConfigRules $configRules
     * @param \Feedaty\Badge\Helper\Orders $ordersHelper
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Psr\Log\LoggerInterface                      $logger,
        \Feedaty\Badge\Model\Config\Source\WebService $webService,
        \Magento\Review\Model\ReviewFactory           $reviewFactory,
        \Magento\Review\Model\RatingFactory           $ratingFactory,
        \Magento\Store\Model\StoreManagerInterface    $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime   $date,
        ConfigRules $configRules,
        \Feedaty\Badge\Helper\Orders $ordersHelper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->_logger = $logger;
        $this->_webService = $webService;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_configRules = $configRules;
        $this->_orderRepository = $orderRepository;
        $this->ordersHelper = $ordersHelper;
        $this->productRepository = $productRepository;
    }


    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {


        //Starter Log
        $this->_logger->addInfo("START - Cronjob Feedaty | Set Feedaty Orders  | date: " . date('Y-m-d H:i:s') );
        //$orderStatus = explode(',', 'complete,processing');
        //$orderStatus = explode(',', $this->_dataHelper->getOrderStatusFilter());
        // $value = ;

        $orders = $this->ordersHelper->getOrders();


         $data = array();

        /* pass data array to write in csv file */
        $i = 0;
        foreach ($orders as $order){

            $items = $order->getItems();

            $storeId =  $order->getStoreId();
            /**
             * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
             */
            $localeCode = $this->ordersHelper->getCulture($storeId);

            $data[$i] = [

                'ID' => $order->getIncrementId(),
                'Date' => $order->getCreatedAt(),
                'CustomerEmail' => $order->getCustomerEmail(),
                'CustomerID' => $order->getCustomerEmail(),
                'Culture' => $localeCode,
                'Platform' => $this->ordersHelper->getPlatform(),
                'Products' => []

            ];

            foreach ($items as $item){
                $itemType = $item->getProductType();

                $sku = $item->getSku();
                $productId = $item->getProductId();
                $productUrl = $item->getProduct()->getProductUrl();

                    array_push($data[$i]['Products'],
                        [
                            'SKU' => $productId,
                            'URL' => $productUrl,
                        ]
                    );


            }

            $i++;
        }
        $this->_logger->addInfo("Order List - Cronjob Feedaty | Set Feedaty Orders  | date 1.0.1: " . print_r($data,true) );

    }

}

