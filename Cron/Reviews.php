<?php

namespace Feedaty\Badge\Cron;

use Feedaty\Badge\Helper\ConfigRules;
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
     * @var ConfigRules
     */
    protected $_configRules;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Feedaty\Badge\Model\Config\Source\WebService $webService
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Psr\Log\LoggerInterface                      $logger,
        \Feedaty\Badge\Model\Config\Source\WebService $webService,
        \Magento\Review\Model\ReviewFactory           $reviewFactory,
        \Magento\Review\Model\RatingFactory           $ratingFactory,
        \Magento\Store\Model\StoreManagerInterface    $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime   $date,
        StoreRepositoryInterface                      $storeRepository,
        \Magento\Sales\Api\OrderRepositoryInterface   $orderRepository,
        ConfigRules $configRules
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
        $this->_configRules = $configRules;
    }


    public function disableReview($reviewData)
    {

        //$this->logger->addInfo("FEEDATY DISABLE REVIEW REVIEW DATA: ID REW " . $reviewData['review_id']);
        $review = null;
        try {
            $review = $this->_reviewFactory->create()->load($reviewData['review_id']);
        } catch (\Exception $e) {
            $this->logger->info("FEEDATY ERROR LINE 86 :  " . $e->getMessage());
        }

        if (!is_null($review)) {
          //  $this->logger->addInfo("FEEDATY DISABLE REVIEW OBJECT: ");
            $review->setStatusId(\Magento\Review\Model\Review::STATUS_NOT_APPROVED)->save();
        }

    }


    public function mediateReview($reviewData, $reviewDetail)
    {

        $this->logger->addInfo("FEEDATY MEDIATE REVIEW REVIEW DATA: ID REW " . $reviewData['review_id']);
        $review = null;
        try {
            $review = $this->_reviewFactory->create()->load($reviewData['review_id']);
        } catch (\Exception $e) {
            $this->logger->info("FEEDATY ERROR LINE 86 :  " . $e->getMessage());
        }

        if (!is_null($review)) {
            $this->logger->addInfo("FEEDATY MEDIATE REVIEW OBJECT: ");
            $review->setDetail($reviewDetail)->save();
            $review->setFeedatyProductMediated(1)->save();
        }

    }

    public function getStoreViewIdByOrder($orderId)
    {
      //  $this->logger->addInfo("FEEDATY START GET STORE: " . $orderId);
        $order = null;
        try {
            $order = $this->_orderRepository->get($orderId);

        } catch (\Exception $e) {
            $this->logger->info("FEEDATY ERROR :  " . $e->getMessage());
            $this->logger->info("FEEDATY ERROR : order id does not exist " . $orderId);

        }


        if (!is_null($order)) {
            $websiteId = $order->getStore()->getWebsiteId();
            $this->logger->addInfo("FEEDATY ORDER websiteId: " . $websiteId);
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

       // $this->logger->addInfo("STORES LIST: " . print_r($storeList, true));

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

        //  $collection->getSelect()->group('feedaty_source_id');
        return count($collection->getData());
    }

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

        //  $collection->getSelect()->group('feedaty_source_id');
        return count($collection->getData());
    }

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

        //  $collection->getSelect()->group('feedaty_source_id');
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {

        /**
         *  IF IMPORT IS ENABLED
         */
        $enableImportReviews = $this->_configRules->getCreateReviewEnabled();

        if($enableImportReviews == 1){

            /**
             * Get Feedaty TotalProductReviews Count
             */
            $totalFeedatyReviews = $this->_webService->getTotalProductReviewsCount();

            /**
             * Get Feedaty Removed Reviews TotalResults Count
             */
            $totalFeedatyRemovedReviews = $this->_webService->getTotalProductRemovedReviewsCount();


            /**
             * Get Feedaty Removed Reviews TotalResults Count
             */
            $totalFeedatyMediatedReviews = $this->_webService->getTotalProductMediatedReviewsCount();

            /**
             * Get Feedaty Reviews Created on Magento Total Count
             */
            $totalReviewCreatedCount = $this->getAllFeedatyReviewCount();

            /**
             * Get Feedaty Reviews Removed on Magento Total Count
             */
            $totalRemovedReviewCreatedCount = $this->getAllFeedatyRemovedReviewCount();


            /**
             * Get Feedaty Reviews Removed on Magento Total Count
             */
            $totalMediatedReviewCreatedCount = $this->getAllFeedatyMediatedReviewCount();


            $this->logger->addInfo("START - Cronjob Feedaty | Get Feedaty Reviews  | date: " . date('Y-m-d H:i:s') . '  ---- Total Feedaty Product Reviews ' . $totalFeedatyReviews . '  totalReviewCreatedCount group by feedaty_id ' . $totalReviewCreatedCount);

            /**
             * Get Last Review Created on Magento (on first run vill be null)
             */
            $lastReviewCreated = $this->getLastFeedatyReviewCreated();

            /**
             * Get n Reviews from Feedaty
             */
            $count = 100;

            /**
             * Set Row Param from Feedaty
             */
            $row = $totalFeedatyReviews - $totalReviewCreatedCount - $count;

            $this->logger->addInfo("MEDIATED LINE 339  " );

            /**
             * CREATE NEW REVIEWS
             */
            //Get Feedaty Product Reviews Data
            $feedatyProductReviews = $this->_webService->getProductReviewsPagination($row, $count);

            $this->logger->addInfo("MEDIATED LINE 347  " );

            if (!empty($feedatyProductReviews)) {
                //Foreach Review
                foreach ($feedatyProductReviews as $review) {
                    //feedaty_source_id
                    $feedatyId = $review['ID'];

                    $orderId = $review['OrderID'];

                    $storeView = $this->getStoreViewIdByOrder($orderId);

                    $replaceFromDate = ["/Date(", ")/"];
                    $replaceToDate = ["", ""];
                    //Review Date
                    $createdAt = date('Y-m-d h:i:s', floor((int)str_replace($replaceFromDate, $replaceToDate, $review['Released']) / 1000));
                    //$reviewReleased =date('Y-m-d h:i:s', floor((int)str_replace($replaceFromDate,$replaceToDate,$review['Released'])/ 1000));

                    $today = $this->date->gmtDate();
                    foreach ($review['ProductsReviews'] as $item) {

                        $productId = $item['SKU'];
                        $feedatyProductReviewId = $item['ID'];

                        //Get Feedaty Product Reviews
                        $magentoProductReviews = $this->getReviewCollection($productId, $feedatyId);

                        //AP Rating node
                        $rating = $item['Rating'];

                        //API Review Node
                        $detail = $item['Review'];

                        //TODO VERIFICARE  website view

                        if (empty($magentoProductReviews)) {
                            $this->createProductReview($productId, $feedatyId, $feedatyProductReviewId, $rating, $detail, $createdAt, $today, $row, $storeView);

                            // Product Review
                        //    $this->logger->addInfo("EXEC - Cronjob Feedaty create Product Review | date execution: " . date('Y-m-d H:i:s') . " | Product Id : " . $productId . " | Feedaty ID" . $feedatyId);

                        }
                    }
                }
                // General Cron Report on system.log
                $this->logger->addInfo("END - Cronjob Feedaty is executed | Get Feedaty Reviews  | date execution: " . date('Y-m-d H:i:s') . " -- Last Review Created " . print_r($lastReviewCreated, true) . " -- Review Date CreatedAt " . print_r($createdAt, true) . " -- Pagination " . $row);
            }

            /**
             *  UPDATE MEDIATED REVIEWS
             */

            $this->logger->addInfo("MEDIATED LINE 397  " );

            $rowMediated = $totalFeedatyMediatedReviews - $totalMediatedReviewCreatedCount - $count;

            if($rowMediated < 0 ){
                $this->logger->addInfo("ROW MEDIATED IS ZERO  " );
                $rowMediated = 0;
            }
            $this->logger->addInfo("MEDIATED ITEMS ROWS  " .$rowMediated);
            $this->logger->addInfo("MEDIATED ITEMS totalFeedatyMediatedReviews " . $totalFeedatyMediatedReviews);
            $this->logger->addInfo("MEDIATED ITEMS totalMediatedReviewCreatedCount " . $totalMediatedReviewCreatedCount);

            /**
             * Get Feedaty Mediated Reviews
             */
            $feedatyProductReviewsMediated = $this->_webService->getMediatedReviews($rowMediated, $count);

            $this->logger->addInfo("MEDIATED ITEMS feedatyProductReviewsMediated " . print_r($feedatyProductReviewsMediated));

            if (!empty($feedatyProductReviewsMediated)) {

                $this->logger->addInfo("START MEDIATED - Cronjob Feedaty is executed | Disabled Feedaty Reviews ID | date execution: " . print_r($feedatyProductReviewsMediated,true));

                //Foreach Review
                foreach ($feedatyProductReviewsMediated as $mediatedReview) {

                    $feedatyId = $mediatedReview['FeedbackReviewID'];
                    $reviewDetail = $mediatedReview['MerchantReview'];

                    $reviewToMediate = $this->getAllReviewsByFeedatyId($feedatyId);
              //      $this->logger->addInfo("REVIEW TO DISABLE OBJ - Cronjob Feedaty is executed | Disabled Feedaty Reviews ID | date execution: " . date('Y-m-d H:i:s') . " -- REVIEWS TO DISABLE " . print_r($reviewToDisable, true));

                    if (!empty($reviewToMediate)) {
                        //if it is not just disabled
                        foreach ($reviewToMediate as $item){
//                            $this->logger->addInfo("REVIEW TO DISABLE ITEM LIN 341 - Cronjob Feedaty is executed | Disabled Feedaty Reviews ID | date execution: " . print_r($item, true));
//                            $this->logger->addInfo("ITEM ID TO DISABLE 357 : " . $item['review_id']);
//                            $this->logger->addInfo("ITEM STATUS TO DISABLE 357 : " . $item['status_id']);

                            if ($item['status_id'] !== 3) {
                                $this->logger->addInfo("STATUS ENALBED");

                                $this->mediateReview($item,$reviewDetail);
                                $this->logger->addInfo("MEDIATED ITEM ID - : " . date('Y-m-d H:i:s') . " -- FeedatySourceID " . $feedatyId);
                            }
                        }

                    }

                }
                // General Cron Report on system.log
            }


            /**
             *  DISABLE REMOVED REVIEWS
             */

            $rowRemoved = $totalFeedatyRemovedReviews - $totalRemovedReviewCreatedCount - $count;

            if($rowRemoved < 0 ){
                $this->logger->addInfo("ROW REMOVED IS ZERO  " );
                $rowRemoved = 0;
            }
//            $this->logger->addInfo("REMOVED ITEMS ROWS  " . $rowRemoved);
//            $this->logger->addInfo("REMOVED ITEMS totalFeedatyRemovedReviews  " . $totalFeedatyRemovedReviews);
//            $this->logger->addInfo("REMOVED ITEMS ROWS totalRemovedReviewCreatedCount " . $totalRemovedReviewCreatedCount);
//
             /**
             * Get Feedaty Removed Reviews
             */
            $feedatyProductReviewsRemoved = $this->_webService->getRemovedReviews($rowRemoved, $count);


            if (!empty($feedatyProductReviewsRemoved)) {

              //  $this->logger->addInfo("START REMOVED - Cronjob Feedaty is executed | Disabled Feedaty Reviews ID | date execution: " . print_r($feedatyProductReviewsRemoved,true));

                //Foreach Review
                foreach ($feedatyProductReviewsRemoved as $removedReview) {

                    $feedatyId = $removedReview['FeedbackReviewID'];

                    $reviewToDisable = $this->getAllReviewsByFeedatyId($feedatyId);
                    //      $this->logger->addInfo("REVIEW TO DISABLE OBJ - Cronjob Feedaty is executed | Disabled Feedaty Reviews ID | date execution: " . date('Y-m-d H:i:s') . " -- REVIEWS TO DISABLE " . print_r($reviewToDisable, true));

                    if (!empty($reviewToDisable)) {
                        //if it is not just disabled
                        foreach ($reviewToDisable as $item){
//                            $this->logger->addInfo("REVIEW TO DISABLE ITEM LIN 341 - Cronjob Feedaty is executed | Disabled Feedaty Reviews ID | date execution: " . print_r($item, true));
//                            $this->logger->addInfo("ITEM ID TO DISABLE 357 : " . $item['review_id']);
//                            $this->logger->addInfo("ITEM STATUS TO DISABLE 357 : " . $item['status_id']);

                            if ($item['status_id'] !== 3) {
                          //      $this->logger->addInfo("STATUS ENALBED");

                                $this->disableReview($item);
                        //        $this->logger->addInfo("REMOVED ITEM ID - : " . date('Y-m-d H:i:s') . " -- FeedatySourceID " . $feedatyId);
                            }
                        }

                    }

                }
                // General Cron Report on system.log
            }
        }
        else{
            $this->logger->addInfo("Feedaty Cronjob is not enabled | date: ". date('Y-m-d H:i:s') );
        }

    }


    protected function createProductReview($productId, $feedatyId, $feedatyProductReviewId, $rating, $detail, $createdAt, $today, $row, $storeView)
    {

        $reviewFinalData['ratings'][1] = $rating;
        $reviewFinalData['ratings'][2] = $rating;
        $reviewFinalData['ratings'][3] = $rating;
        $reviewFinalData['ratings'][4] = $rating;

        $reviewFinalData['nickname'] = "Feedaty";
        $reviewFinalData['title'] = "Acquirente Verificato";
        $reviewFinalData['detail'] = $detail;
        $reviewFinalData['feedaty_source'] = 1;
        $reviewFinalData['feedaty_pagination'] = $row;
        $reviewFinalData['feedaty_source_id'] = $feedatyId;
        $reviewFinalData['feedaty_product_review_id'] = $feedatyProductReviewId;
        $reviewFinalData['feedaty_create'] = $today;
        $reviewFinalData['feedaty_update'] = $today;
        $reviewFinalData['feedaty_product_mediated'] = 0;
        $review = $this->_reviewFactory->create()->setData($reviewFinalData);

        $review->unsetData('review_id');
        $review->setEntityId($review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE))
            ->setEntityPkValue($productId)
            ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setStoreId($this->_storeManager->getStore()->getId())
            //->setStores(!is_null($storeView) ?  [$storeView] : $this->_storeManager->getStore()->getId())
            ->setStores(!is_null($storeView) ? [$storeView] : [0,1])
            ->save();

        //Since the created_at is set only when the $object does not have an id, i save the object again.
        $review->setCreatedAt($createdAt)->save();
        //$review->setCreatedAt('2018-07-31 11:30:05')->save();

        foreach ($reviewFinalData['ratings'] as $ratingId => $optionId) {
            $this->_ratingFactory->create()
                ->setRatingId($ratingId)
                ->setReviewId($review->getId())
                ->setRatingSummary(4)
                ->addOptionVote($optionId, $productId);
        }

        $review->aggregate();
    }
}

