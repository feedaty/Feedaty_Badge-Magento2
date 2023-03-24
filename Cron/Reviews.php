<?php

namespace Feedaty\Badge\Cron;

use Feedaty\Badge\Helper\ConfigRules;
use Feedaty\Badge\Helper\Data;
use Feedaty\Badge\Helper\Reviews as ReviewsHelper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;


class Reviews
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
    protected $_reviewsHelper;


    protected $dataHelper;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Feedaty\Badge\Model\Config\Source\WebService $webService
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param ConfigRules $configRules
     * @param ReviewsHelper $reviewsHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface                      $logger,
        \Feedaty\Badge\Model\Config\Source\WebService $webService,
        \Magento\Review\Model\ReviewFactory           $reviewFactory,
        \Magento\Review\Model\RatingFactory           $ratingFactory,
        \Magento\Store\Model\StoreManagerInterface    $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime   $date,
        ConfigRules $configRules,
        ReviewsHelper $reviewsHelper,
        Data $dataHelper
    )
    {
        $this->_logger = $logger;
        $this->_webService = $webService;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_configRules = $configRules;
        $this->_reviewsHelper = $reviewsHelper;
        $this->dataHelper = $dataHelper;
    }


    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {


        /**
         * Get stores
         */
        $storesIds = $this->dataHelper->getAllStoresIds();

        foreach ($storesIds as $storeId) {
            /**
             *  IF IMPORT IS ENABLED
             */
            $enableImportReviews = $this->_configRules->getCreateReviewEnabled($storeId);

            if($enableImportReviews == 1){

                /**
                 * Get Feedaty TotalProductReviews Count
                 */
                $totalFeedatyReviews = $this->_webService->getTotalProductReviewsCount($storeId);

                /**
                 * Get Feedaty Removed Reviews TotalResults Count
                 */
                $totalFeedatyRemovedReviews = $this->_webService->getTotalProductRemovedReviewsCount($storeId);

                /**
                 * Get Feedaty Mediated Reviews TotalResults Count
                 */
                $totalFeedatyMediatedReviews = $this->_webService->getTotalProductMediatedReviewsCount($storeId);

                /**
                 * Get Feedaty Reviews Created on Magento Total Count
                 */
                $totalReviewCreatedCount = $this->_reviewsHelper->getAllFeedatyReviewCount($storeId);

                /**
                 * Get Feedaty Reviews Removed on Magento Total Count
                 */
                $totalRemovedReviewCreatedCount = $this->_reviewsHelper->getAllFeedatyRemovedReviewCount($storeId);

                /**
                 * Get Feedaty Reviews Removed on Magento Total Count
                 */
                $totalMediatedReviewCreatedCount = $this->_reviewsHelper->getAllFeedatyMediatedReviewCount($storeId);

                /**
                 * Get Last Review Created on Magento (on first run will be null)
                 */
                $lastReviewCreated = $this->_reviewsHelper->getLastFeedatyReviewCreated($storeId);

                /**
                 * Get n Reviews from Feedaty
                 */
                $count = 100;

                /**
                 * Set Row Param from Feedaty
                 */
                $row = $totalFeedatyReviews - $totalReviewCreatedCount - $count;


                /**
                 * CREATE NEW REVIEWS
                 */
                //Get Feedaty Product Reviews Data
                $feedatyProductReviews = $this->_webService->getProductReviewsPagination($row, $count, $storeId);

                $debugMode = $this->_configRules->getDebugModeEnabled($storeId);

                if($debugMode === "1") {
                    $this->_logger->info("Feedaty Debug Mode | Cronjob Run | Get Feedaty Reviews  | date: " . date('Y-m-d H:i:s') . '  ---- Total Feedaty Product Reviews ' . $totalFeedatyReviews . '  totalReviewCreatedCount group by feedaty_id ' . $totalReviewCreatedCount . 'Feedaty Product Reviews Data'. print_r($feedatyProductReviews,true));
                }

                if (!empty($feedatyProductReviews)) {

                    //Foreach Review
                    foreach ($feedatyProductReviews as $review) {
                        //feedaty_source_id
                        $feedatyId = $review['ID'];

                        $orderId = $review['OrderID'];

                        $storeView = $this->_reviewsHelper->getStoreViewIdByOrder($orderId, $storeId);

                        $replaceFromDate = ["/Date(", ")/"];
                        $replaceToDate = ["", ""];
                        //Review Date
                        $createdAt = date('Y-m-d h:i:s', floor((int)str_replace($replaceFromDate, $replaceToDate, $review['Released']) / 1000));

                        $today = $this->_date->gmtDate();
                        foreach ($review['ProductsReviews'] as $item) {

                            $productId = $item['SKU'];
                            $feedatyProductReviewId = $item['ID'];

                            //Get Feedaty Product Reviews
                            $magentoProductReviews = $this->_reviewsHelper->getReviewCollection($feedatyProductReviewId);

                            //AP Rating node
                            $rating = $item['Rating'];

                            //API Review Node
                            $detail = $item['Review'];

                            //If review does not exist
                            if (empty($magentoProductReviews)) {
                                $this->createProductReview($productId, $feedatyId, $feedatyProductReviewId, $rating, $detail, $createdAt, $today, $row, $storeView, $storeId);
                            }
                        }
                    }
                    // General Cron Report on system.log
                    $this->_logger->info("Feedaty | END Cronjob | Get Feedaty Reviews  | date execution: " . date('Y-m-d H:i:s') );
                }

                /**
                 *  UPDATE MEDIATED REVIEWS
                 */

                $rowMediated = $totalFeedatyMediatedReviews - $totalMediatedReviewCreatedCount - $count;

                if($rowMediated < 0 ){

                    $rowMediated = 0;
                }

                /**
                 * Get Feedaty Mediated Reviews
                 */
                $feedatyProductReviewsMediated = $this->_webService->getMediatedReviews($rowMediated, $count, $storeId);


                if (!empty($feedatyProductReviewsMediated)) {

                    $this->_logger->info("Feedaty | START Mediated Reviews");

                    //Foreach Review
                    foreach ($feedatyProductReviewsMediated as $mediatedReview) {

                        $feedatyId = $mediatedReview['FeedbackReviewID'];
                        $reviewDetail = $mediatedReview['MerchantReview'];

                        $reviewToMediate = $this->_reviewsHelper->getAllReviewsByFeedatyId($feedatyId, $storeId);

                        if (!empty($reviewToMediate)) {
                            //if it is not just disabled
                            foreach ($reviewToMediate as $item){

                                if ($item['status_id'] !== 3) {
                                    $this->_reviewsHelper->mediateReview($item,$reviewDetail);
                                }
                            }

                        }

                    }
                }


                /**
                 *  DISABLE REMOVED REVIEWS
                 */
                $rowRemoved = $totalFeedatyRemovedReviews - $totalRemovedReviewCreatedCount - $count;

                if($rowRemoved < 0 ){
                    $rowRemoved = 0;
                }

                /**
                 * Get Feedaty Removed Reviews
                 */
                $feedatyProductReviewsRemoved = $this->_webService->getRemovedReviews($rowRemoved, $count, $storeId);

                if (!empty($feedatyProductReviewsRemoved)) {

                    //Foreach Review
                    foreach ($feedatyProductReviewsRemoved as $removedReview) {

                        $feedatyId = $removedReview['FeedbackReviewID'];

                        $reviewToDisable = $this->_reviewsHelper->getAllReviewsByFeedatyId($feedatyId, $storeId);

                        if (!empty($reviewToDisable)) {
                            //if it is not just disabled
                            foreach ($reviewToDisable as $item){

                                if ($item['status_id'] !== 3) {
                                    $this->_reviewsHelper->disableReview($item);
                                }
                            }

                        }

                    }
                    // General Cron Report on system.log
                }
            }
            else{
                $this->_logger->info("Feedaty | Cronjob is not enabled. Configure it from admin panel | date: ". date('Y-m-d H:i:s') );
            }
        }


    }


    /**
     * Create Reviews on Magento
     * @param $productId
     * @param $feedatyId
     * @param $feedatyProductReviewId
     * @param $rating
     * @param $detail
     * @param $createdAt
     * @param $today
     * @param $row
     * @param $storeView
     * @param $storeId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function createProductReview($productId, $feedatyId, $feedatyProductReviewId, $rating, $detail, $createdAt, $today, $row, $storeView, $storeId)
    {

        $ratingCollection = $this->_reviewsHelper->getRatingCollection();

        foreach ($ratingCollection as $ratingItem){

            $reviewFinalData['ratings'][$ratingItem['rating_id']] = $rating;
        }

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
            ->setStoreId($storeId)
            ->setStores(!is_null($storeView) ? [$storeView] : [$this->_storeManager->getStore()->getId()])
            ->setCustomerId(null)
            ->save();

        foreach ($reviewFinalData['ratings']  as $ratingId => $ratingValue) {

            $this->_ratingFactory->create()
                ->setRatingId($ratingId)
                ->setReviewId($review->getId())
                ->addOptionVote($ratingValue, $productId);
        }

        $review->aggregate();

        $review->setCreatedAt($createdAt)->save();
    }
}

