<?php

namespace Feedaty\Badge\Cron;

use Feedaty\Badge\Helper\Data;
use Feedaty\Badge\Helper\Orders as OrdersHelper;
use Feedaty\Badge\Model\Config\Source\WebService;
use Magento\Framework\Url;
use Psr\Log\LoggerInterface;
use Feedaty\Badge\Helper\ConfigRules;
use \Magento\Store\Model\StoreManagerInterface;

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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;


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
        Url $url,
        StoreManagerInterface  $storeManager
    )
    {
        $this->_logger = $logger;
        $this->ordersHelper = $ordersHelper;
        $this->webService = $webService;
        $this->dataHelper = $dataHelper;
        $this->_configRules = $configRules;
        $this->url = $url;
        $this->storeManager = $storeManager;
    }

    /**
     * Send Orders to Feedaty Orders API
     */
    public function execute()
    {

        /**
         * Get All Stores Ids
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

            /**
             * Get HistoryOrders
             */
            $ordersHistory = $this->ordersHelper->getHistoryOrders($storeId);

            /**
             * Send Orders History to Feedaty
             */
            if(count($ordersHistory) > 0){
                $this->ordersHelper->sendFeedatyOrders($ordersHistory, $storeId, true);
            }

            /**
             * Send Orders to Feedaty
             */
            if(count($orders) > 0){
                $this->ordersHelper->sendFeedatyOrders($orders, $storeId, false);
            }
        }


    }

}

