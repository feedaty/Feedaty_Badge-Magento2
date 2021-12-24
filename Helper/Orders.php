<?php

declare(strict_types=1);

namespace Feedaty\Badge\Helper;

use Feedaty\Badge\Helper\ConfigRules;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
class Orders extends AbstractHelper
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;
    /**
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $_ratingFactory;

    /**
     * @var StoreRepositoryInterface
     */
    protected $_storeRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Feedaty\Badge\Helper\ConfigSetting
     */
    protected $_helperConfigRules;


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;


    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;


    protected $productMetadata;

    protected  $productFactory;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param StoreRepositoryInterface $storeRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Feedaty\Badge\Helper\ConfigRules $helperConfigRules
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param ProductMetadataInterface $productMetadata
     * @param ProductFactory $productFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface                      $logger,
        \Magento\Review\Model\ReviewFactory           $reviewFactory,
        \Magento\Review\Model\RatingFactory           $ratingFactory,
        StoreRepositoryInterface                      $storeRepository,
        \Magento\Sales\Api\OrderRepositoryInterface   $orderRepository,
        ConfigRules $helperConfigRules,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepositoryInterface $productRepository,
        ProductMetadataInterface $productMetadata,
        ProductFactory $productFactory

    )
    {
        $this->logger = $logger;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_storeRepository = $storeRepository;
        $this->orderRepository = $orderRepository;
        $this->_helperConfigRules = $helperConfigRules;
        $this->scopeConfig = $scopeConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productRepository = $productRepository;
        $this->productMetadata = $productMetadata;
        $this->productFactory = $productFactory;
    }

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

        $culture = strtok($locale, '/');

        $allowedLanguages = array("it", "en", "es", "fr", "de");

        //Set default language fallback
        if (!in_array($culture, $allowedLanguages)) {
            $culture = "en";
        }

        return $culture;
    }


    public function getOrders()
    {
        $criteria = $this->searchCriteriaBuilder
          //  ->addFilter('status','processing','eq')
            // ->addFilter('updated_at', $this->_ordersHelper->getOrderFromDate(),'gteq')
            // ->addFilter('updated_at',$to,'lteq')
            ->setPageSize(500)
            ->setCurrentPage(1)
            ->create();
        $orderResult = $this->orderRepository->getList($criteria);

        $orders = $orderResult->getItems();

        return $orders;
    }



    public function getPlatform()
    {
        $platform = $this->productMetadata->getName() . ' ' .  $this->productMetadata->getEdition() . ' ' .  $this->productMetadata->getVersion();
        return $platform;
    }





}
