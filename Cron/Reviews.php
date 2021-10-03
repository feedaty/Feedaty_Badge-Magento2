<?php

namespace Feedaty\Badge\Cron;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;


class Reviews
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
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
    protected $date;


    /**
     * @var StoreRepositoryInterface
     */
    private $_storeRepository;


    protected $_orderRepository;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Feedaty\Badge\Model\Config\Source\WebService $webService
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Feedaty\Badge\Model\Config\Source\WebService $webService,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        StoreRepositoryInterface $storeRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    )
    {
        $this->logger = $logger;
        $this->_webService = $webService;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_storeManager = $storeManager;
        $this->date = $date;
        $this->_storeRepository = $storeRepository;
        $this->_orderRepository = $orderRepository;
    }


    public function disableReview($reviewData){

        $review = $this->_reviewFactory->create()->load($reviewData[0]['review_id']);
        $review->setStatusId(\Magento\Review\Model\Review::STATUS_NOT_APPROVED)->save();;

    }

    public function getStoreViewIdByOrder($orderId)
    {
        $this->logger->addInfo("FEEDATY START GET STORE: ".$orderId );
        $order = null;
        try {
            $order = $this->_orderRepository->get($orderId);

        } catch (\Exception $e) {
            $this->logger->info("FEEDATY ERROR :  ".$e->getMessage());
            $this->logger->info("FEEDATY ERROR : order id does not exist ".$orderId);

        }


        if(!is_null($order)){
            $websiteId = $order->getStore()->getWebsiteId();
            $this->logger->addInfo("FEEDATY ORDER websiteId: " .$websiteId);
            return $websiteId;
        }

        return null;

    }

    function getAllStoreList()
    {
        $storeList = $this->_storeRepository->getList();

        $storeIds = array();
        foreach ($storeList as $store) {
            $storeIds = $store->getStoreId(); // store id
        }

       $this->logger->addInfo("STORES LIST: " .print_r($storeList,true) );

        return $storeIds;
    }


    /**
     * @param $productId
     * @param $feedatyId
     * @return mixed
     */
    public function getReviewCollection($productId, $feedatyId){
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
    public function getAllFeedatyReviewCount(){
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

      //  $collection->getSelect()->group('feedaty_source_id');
        return count($collection->getData());
    }

    public function getAllFeedatyRemovedReviewCount(){
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

        //  $collection->getSelect()->group('feedaty_source_id');
        return count($collection->getData());
    }

    /*
     * Get last Review Created
     */
    public function getLastFeedatyReviewCreated(){
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

    public function getReviewByFeedatyId($feedatyid){
        $collection = $this->_reviewFactory->create()->getCollection()
            ->addFieldToFilter(
                'feedaty_source_id',
                $feedatyid
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
     * Review Data
     *
     * @return Review
     */
    public function getReviewData($reviewId)
    {
        try {
            $review = $this->_reviewFactory->create()->load($reviewId);
        } catch (LocalizedException $exception) {
            throw new LocalizedException(__($exception->getMessage()));
        }
        return $review;
    }
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {

        $totalFeedatyReviews = $this->_webService->getTotalProductReviewsCount();
        $totalFeedatyRemovedReviews = $this->_webService->getTotalProductRemovedReviewsCount();
        $totalReviewCreatedCount = $this->getAllFeedatyReviewCount();
        $totalRemovedReviewCreatedCount = $this->getAllFeedatyRemovedReviewCount();
        $this->logger->addInfo("START - Cronjob Feedaty | Get Feedaty Reviews  | date: " . date('Y-m-d H:i:s') . '  ---- Total Feedaty Product Reviews ' . $totalFeedatyReviews . '  totalReviewCreatedCount group by feedaty_id ' . $totalReviewCreatedCount );
       // $this->logger->addInfo("START - Cronjob Feedaty | STORES  | date: " . $this->getAllStoreList() );

        //Get Last Review Created on Magento (on first run vill be null)
        $lastReviewCreated = $this->getLastFeedatyReviewCreated();

        $count = 100; // get x reviews

        $row = $totalFeedatyReviews - $totalReviewCreatedCount - $count;


        ///// CREATE NEW REVIEWS
        //Get Feedaty Product Reviews
        $feedatyProductReviews = $this->getProductReviewsPagination($row,$count);
        if(!empty($feedatyProductReviews)){
            //Foreach Review
            foreach ($feedatyProductReviews as $review){
                //feedaty_source_id
                $feedatyId = $review['ID'];

                $orderId = $review['OrderID'];

                $storeView = $this->getStoreViewIdByOrder($orderId);

                $replaceFromDate = ["/Date(", ")/"];
                $replaceToDate = ["", ""];
                //Review Date
                $createdAt = date('Y-m-d h:i:s', floor((int)str_replace($replaceFromDate,$replaceToDate,$review['Released'])/ 1000));
                //$reviewReleased =date('Y-m-d h:i:s', floor((int)str_replace($replaceFromDate,$replaceToDate,$review['Released'])/ 1000));

                $today = $this->date->gmtDate();
                foreach ($review['ProductsReviews'] as $item){

                    $productId = $item['SKU'];

                    //Get Feedaty Product Reviews
                    $magentoProductReviews = $this->getReviewCollection($productId, $feedatyId);

                    //AP Rating node
                    $rating = $item['Rating'];

                    //API Review Node
                    $detail = $item['Review'];

                    //TODO VERIFICARE  website view

                    if (empty($magentoProductReviews)) {
                       $this->createProductReview($productId, $feedatyId, $rating, $detail, $createdAt,$today,$row,$storeView);

                        // Product Review
                        $this->logger->addInfo("EXEC - Cronjob Feedaty create Product Review | date execution: ". date('Y-m-d H:i:s') ." | Product Id : ". $productId . " | Feedaty ID" .$feedatyId );

                    }
                }
            }
            // General Cron Report on system.log
            $this->logger->addInfo("END - Cronjob Feedaty is executed | Get Feedaty Reviews  | date execution: ". date('Y-m-d H:i:s') ." -- Last Review Created " . print_r($lastReviewCreated,true) ." -- Review Date CreatedAt " . print_r($createdAt,true) ." -- Pagination " . $row);
        }

        ///// DISABLE REMOVED REVIEWS
        ///
        $rowRemoved = $totalFeedatyRemovedReviews - $totalRemovedReviewCreatedCount - $count;

        $this->logger->addInfo("REMOVED ITEMS ROWS  ".$rowRemoved);
        $this->logger->addInfo("REMOVED ITEMS totalFeedatyRemovedReviews  ".$totalFeedatyRemovedReviews);
        $this->logger->addInfo("REMOVED ITEMS ROWS totalRemovedReviewCreatedCount ".$totalRemovedReviewCreatedCount);

        $feedatyProductReviewsRemoved = $this->getRemovedReviews($rowRemoved,$count);
        if(!empty($feedatyProductReviewsRemoved)){

            $this->logger->addInfo("START REMOVED - Cronjob Feedaty is executed | Disabled Feedaty Reviews ID | date execution: ". date('Y-m-d H:i:s') );

            //Foreach Review
            foreach ($feedatyProductReviewsRemoved as $removedReview){

                $feedatyId = $removedReview['MerchantFeedbackReviewID'];

                $reviewToDisable = $this->getReviewByFeedatyId($feedatyId);
                $this->logger->addInfo("REVIEW TO DISABLE OBJ - Cronjob Feedaty is executed | Disabled Feedaty Reviews ID | date execution: ". date('Y-m-d H:i:s') ." -- REVIEWS TO DISABLE " . print_r($reviewToDisable,true));

                if(!empty($reviewToDisable)){
                    //if it is not just disabled
                    if($reviewToDisable[0]['status_id'] != 3){
                        $this->disableReview($reviewToDisable);
                        $this->logger->addInfo("REMOVED ITEM ID - : ". date('Y-m-d H:i:s') ." -- FeedatySourceID " . $feedatyId);
                    }


                }

            }
            // General Cron Report on system.log
        }


      // $this->logger->addInfo("END - Cronjob Feedaty is executed | Get Feedaty Reviews  | date: ". date('Y-m-d H:i:s') );
    }

    public function getProductReviewsPagination($row, $count)
    {
        return $this->_webService->getProductReviewsPagination($row, $count);
    }

    public function getRemovedReviews($row, $count)
    {
       return $this->_webService->getRemovedReviews($row, $count);
    }


    protected function createProductReview($productId, $feedatyId, $rating, $detail, $createdAt,$today,$row,$storeView){

        $reviewFinalData['ratings'][1] = $rating;
        $reviewFinalData['ratings'][2] = $rating;
        $reviewFinalData['ratings'][3] = $rating;

        $reviewFinalData['nickname'] = "Feedaty";
        $reviewFinalData['title'] = "Acquirente Verificato";
        $reviewFinalData['detail'] = $detail;
        $reviewFinalData['feedaty_source'] = 1;
        $reviewFinalData['feedaty_pagination'] = $row;
        $reviewFinalData['feedaty_source_id'] = $feedatyId;
        $reviewFinalData['feedaty_create'] = $today;
        $reviewFinalData['feedaty_update'] = $today;
        $review = $this->_reviewFactory->create()->setData($reviewFinalData);

        $review->unsetData('review_id');
        $review->setEntityId($review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE))
            ->setEntityPkValue($productId)
            ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setStoreId($this->_storeManager->getStore()->getId())
            //->setStores(!is_null($storeView) ?  [$storeView] : $this->_storeManager->getStore()->getId())
            ->setStores(!is_null($storeView) ?  [$storeView] : [0,1,2])
            ->save();

        //Since the created_at is set only when the $object does not have an id, i save the object again.
        $review->setCreatedAt($createdAt)->save();
        //$review->setCreatedAt('2018-07-31 11:30:05')->save();

        foreach ($reviewFinalData['ratings'] as $ratingId => $optionId) {
            $this->_ratingFactory->create()
                ->setRatingId($ratingId)
                ->setReviewId($review->getId())
                ->addOptionVote($optionId, $productId);
        }

        $review->aggregate();
    }
}

