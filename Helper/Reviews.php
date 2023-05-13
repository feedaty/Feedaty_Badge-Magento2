<?php

namespace Feedaty\Badge\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\ResourceModel\Rating\CollectionFactory as RatingCollectionFactory;


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
     * @var CollectionFactory
     */
    private $reviewCollection;
    /**
     * @var RatingCollectionFactory
     */
    private $ratingCollectionFactory;

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
        ConfigRules $helperConfigRules,
        CollectionFactory       $reviewCollection,
        RatingCollectionFactory $ratingCollectionFactory

    )
    {
        $this->_logger = $logger;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_storeRepository = $storeRepository;
        $this->_orderRepository = $orderRepository;
        $this->_helperConfigRules = $helperConfigRules;
        $this->reviewCollection = $reviewCollection;
        $this->ratingCollectionFactory = $ratingCollectionFactory;
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
            $this->_logger->info("Feedaty | Error can not disable Review :  " . $e->getMessage());
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
            $this->_logger->info("Feedaty | Error cannot mediate Review :  " . $e->getMessage());
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
    public function getStoreViewIdByOrder($orderId, $storeId)
    {
        $forceDefaultStore = $this->_helperConfigRules->getReviewForceDefaultStore($storeId);

        if($forceDefaultStore === "1"){
            return $this->_helperConfigRules->getReviewDefaultStore($storeId);
        }
        else{
            $order = null;
            try {
                $order = $this->_orderRepository->get($orderId);
            }
            catch (\Exception $e) {
                $this->_logger->error("Feedaty | Order id does not exist " . $orderId . " Message". $e->getMessage());
            }

            if (!is_null($order)) {
                return $order->getStore()->getWebsiteId();
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
    public function getReviewCollection($feedatyProductReviewId)
    {

        $collection = $this->reviewCollection->create()
            ->addFieldToFilter(
                'feedaty_product_review_id',
                $feedatyProductReviewId
            )
            ->setDateOrder();

        return $collection->getData();

    }

    /*
     * Get last Review Created
     */
    public function getAllFeedatyReviewCount($storeId)
    {
        $collection = $this->reviewCollection->create()
            ->addFieldToFilter(
                'feedaty_source',
                1
            )
            ->addFieldToFilter(
                'store_id',
                $storeId
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
    public function getAllFeedatyRemovedReviewCount($storeId)
    {
        $collection = $this->reviewCollection->create()
            ->addStatusFilter(
                \Magento\Review\Model\Review::STATUS_NOT_APPROVED)
            ->addFieldToFilter(
                'feedaty_source',
                1
            )
            ->addFieldToFilter(
                'store_id',
                $storeId
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
    public function getAllFeedatyMediatedReviewCount($storeId)
    {
        $collection = $this->reviewCollection->create()
            ->addFieldToFilter(
                'feedaty_product_mediated',
                1
            )
            ->addFieldToFilter(
                'store_id',
                $storeId
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
    public function getLastFeedatyReviewCreated($storeId)
    {
        $collection = $this->reviewCollection->create()
            ->addFieldToFilter(
                'feedaty_source',
                1
            )
            ->addFieldToFilter(
                'store_id',
                $storeId
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
     * @param $feedatyId
     * @return mixed
     */
    public function getAllReviewsByFeedatyId($feedatyId, $storeId)
    {
        $collection = $this->reviewCollection->create()
            ->addFieldToFilter(
                'feedaty_product_review_id',
                $feedatyId
            )
            ->addFieldToFilter(
                'store_id',
                $storeId
            )
            ->setOrder(
                'review_id',
                'desc'
            )
            ->setDateOrder();

        return $collection->getData();
    }

    /**
     * @return array|null
     */
    public function getRatingCollection()
    {
        $ratingCollection = $this->ratingCollectionFactory->create()
            ->addEntityFilter('product')
            ->load();

        return $ratingCollection->getData();
    }


}
