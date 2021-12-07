<?php

namespace Feedaty\Badge\Helper;

use Feedaty\Badge\Helper\ConfigRules;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Api\StoreRepositoryInterface;

class Reviews extends AbstractHelper
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

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
    private $_storeRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var \Feedaty\Badge\Helper\ConfigSetting
     */
    protected $_helperConfigRules;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param StoreRepositoryInterface $storeRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Psr\Log\LoggerInterface                      $logger,
        \Magento\Review\Model\ReviewFactory           $reviewFactory,
        \Magento\Review\Model\RatingFactory           $ratingFactory,
        StoreRepositoryInterface                      $storeRepository,
        \Magento\Sales\Api\OrderRepositoryInterface   $orderRepository,
        ConfigRules $helperConfigRules

    )
    {
        $this->_logger = $logger;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_storeRepository = $storeRepository;
        $this->_orderRepository = $orderRepository;
        $this->_helperConfigRules = $helperConfigRules;
    }

    /**
     * @param $reviewData
     * @throws \Exception
     */
    public function disableReview($reviewData)
    {
        $review = null;
        try {
            $review = $this->_reviewFactory->create()->load($reviewData['review_id']);
        } catch (\Exception $e) {
            $this->_logger->info("Feedaty error disableReview :  " . $e->getMessage());
        }

        if (!is_null($review)) {
            $review->setStatusId(\Magento\Review\Model\Review::STATUS_NOT_APPROVED)->save();
        }
    }

    /**
     * @param $reviewData
     * @param $reviewDetail
     */
    public function mediateReview($reviewData, $reviewDetail)
    {
        $review = null;
        try {
            $review = $this->_reviewFactory->create()->load($reviewData['review_id']);
        } catch (\Exception $e) {
            $this->_logger->info("Feedaty Error mediateReview :  " . $e->getMessage());
        }

        if (!is_null($review)) {
            $review->setDetail($reviewDetail)->save();
            $review->setFeedatyProductMediated(1)->save();
        }

    }

    /**
     * @param $orderId
     * @return int|string|null
     */
    public function getStoreViewIdByOrder($orderId)
    {
        $forceDefaultStore = $this->_helperConfigRules->getReviewForceDefaultStore();

        if($forceDefaultStore === "1"){
            $defaultStore = $this->_helperConfigRules->getReviewDefaultStore();
            return $defaultStore;
        }
        else{
            $order = null;
            try {
                $order = $this->_orderRepository->get($orderId);
            }
            catch (\Exception $e) {
                $this->_logger->info("Feedaty Error : order id does not exist " . $orderId . " Error message". $e->getMessage());
            }

            if (!is_null($order)) {
                $websiteId = $order->getStore()->getWebsiteId();
                return $websiteId;
            }

            return null;
        }


    }

    /**
     * @return array|int
     */
    function getAllStoreList()
    {
        $storeList = $this->_storeRepository->getList();

        $storeIds = array();
        foreach ($storeList as $store) {
            $storeIds = $store->getStoreId(); // store id
        }

        return $storeIds;
    }


    /**
     * @param $productId
     * @param $feedatyId
     * @return mixed
     */
    public function getReviewCollection($productId, $feedatyId)
    {
        $collection = $this->_reviewFactory->create()->getCollection()
            ->addEntityFilter(
                'product',
                $productId
            )
            ->addFieldToFilter(
                'feedaty_source_id',
                $feedatyId
            )
            ->setDateOrder();

        return $collection->getData();
    }

    /*
     * Get last Review Created
     */
    public function getAllFeedatyReviewCount()
    {
        $collection = $this->_reviewFactory->create()->getCollection()
            ->addFieldToFilter(
                'feedaty_source',
                1
            )
            ->setOrder(
                'review_id',
                'desc'
            )
            ->setDateOrder();

        return count($collection->getData());
    }

    /**
     * @return int|void
     */
    public function getAllFeedatyRemovedReviewCount()
    {
        $collection = $this->_reviewFactory->create()->getCollection()
            ->addStatusFilter(
                \Magento\Review\Model\Review::STATUS_NOT_APPROVED)
            ->addFieldToFilter(
                'feedaty_source',
                1
            )
            ->setOrder(
                'review_id',
                'desc'
            )
            ->setDateOrder();

        return count($collection->getData());
    }

    /**
     * @return int|void
     */
    public function getAllFeedatyMediatedReviewCount()
    {
        $collection = $this->_reviewFactory->create()->getCollection()
            ->addFieldToFilter(
                'feedaty_product_mediated',
                1
            )
            ->setOrder(
                'review_id',
                'desc'
            )
            ->setDateOrder();

        return count($collection->getData());
    }

    /*
     * Get last Review Created
     */
    public function getLastFeedatyReviewCreated()
    {
        $collection = $this->_reviewFactory->create()->getCollection()
            ->addFieldToFilter(
                'feedaty_source',
                1
            )
            ->setOrder(
                'review_id',
                'desc'
            )
            ->setPageSize(1)
            ->setCurPage(1)
            ->setDateOrder();

        return $collection->getData();
    }


    /**
     * @param $feedatyid
     * @return mixed
     */
    public function getAllReviewsByFeedatyId($feedatyid)
    {
        $collection = $this->_reviewFactory->create()->getCollection()
            ->addFieldToFilter(
                'feedaty_product_review_id',
                $feedatyid
            )
            ->setOrder(
                'review_id',
                'desc'
            )
            ->setDateOrder();

        return $collection->getData();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRatingCollection(){
        $ratingCollection = $this->_ratingFactory->create()->getResourceCollection()->addEntityFilter('product')->load();
        return $ratingCollection->getData();
    }


}
