<?php

namespace Feedaty\Badge\Controller\Adminhtml\Index;

use Feedaty\Badge\Helper\ConfigRules;
use Feedaty\Badge\Helper\Orders as OrdersHelper;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Url;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\File\Csv;
use \Magento\Framework\App\Filesystem\DirectoryList;
use Psr\Log\LoggerInterface;

class Index extends \Magento\Backend\App\Action
{

    /**
     * @var Magento\Framework\Filesystem
     */
    protected $directoryList;

    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ConfigRules
     */
    protected $configRules;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Magento\Framework\File\Csv
     */
    protected $csvProcessor;


    /**
     * @var OrdersHelper
     */
    protected $ordersHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * @var Url
     */
    private $url;
    private Context $context;
    private Csv $csv;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param PageFactory $resultPageFactory
     * @param ObjectManagerInterface $objectmanager
     * @param Csv $csv
     * @param DirectoryList $directoryList
     * @param OrdersHelper $ordersHelper
     * @param ConfigRules $configRules
     * @param LoggerInterface $logger
     * @param Url $url
     */
    public function __construct(
        Context                $context,
        ScopeConfigInterface   $scopeConfig,
        StoreManagerInterface  $storeManager,
        PageFactory            $resultPageFactory,
        ObjectManagerInterface $objectmanager,
        Csv                    $csv,
        DirectoryList          $directoryList,
        OrdersHelper           $ordersHelper,
        ConfigRules            $configRules,
        LoggerInterface        $logger,
        Url                    $url
    )
    {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->objectManager = $objectmanager;
        $this->csv = $csv;
        $this->directoryList = $directoryList;
        $this->ordersHelper = $ordersHelper;
        $this->context = $context;
        $this->configRules = $configRules;
        $this->logger = $logger;
        $this->url = $url;
    }

    /*
    * Execute
    *
    */
    public function execute()
    {

        # INIT FIELDS
        $request = $this->_request;
        $storeId = (int) $request->getParam('store', 0);

        /**
         * Get Feedaty Order options Status
         */
        $orderStatus = $this->configRules->getSendOrderStatus($storeId);

        /**
         * Get Debug Mode
         */
        $debugMode = $this->configRules->getDebugModeEnabled($storeId);

        /**
         * Get Data Range Options
         */
        $exportDateFrom = $this->configRules->getExportOrdersFrom($storeId);
        $exportDateTo = $this->configRules->getExportOrdersTo($storeId);
        $last4months = date('Y-m-d', strtotime("-4 months"));
        $now = date('Y-m-d', strtotime("+1 days"));
        $from = $exportDateFrom != '' ? $exportDateFrom : $last4months;
        $to = $exportDateTo != '' ? $exportDateTo : $now;

        if ($debugMode === "1") {
            $this->logger->info("Feedaty | Export Orders From Admin Panel | Store ID: " . $storeId . "Order Status " . $orderStatus . "  | date: " . date('Y-m-d H:i:s'));
        }

        $dirPath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $outputFile = $dirPath . "/tmp/FeedatyOrderExport_" . date('Ymd_His') . ".csv";

        if (!is_dir($dirPath))
            mkdir($dirPath, 0777, true);

        $orders = $this->ordersHelper->getCsvOrders($from, $to, $storeId);


        $delimiter = ',';
        $enclosure = '"';

        $heading = ["Order ID", "UserID", "E-mail", "Date", "Product ID", "Extra", "Product Url", "Product Image", "EAN", "Platform"];

        $this->csv->setDelimiter($delimiter);
        $this->csv->setEnclosure($enclosure);

        $data[] = $heading;

        foreach ($orders as $order) {

            $items = $order->getAllVisibleItems();


            foreach ($items as $item) {

                    $product = $item->getProduct();

                    $productId = $product->getId();
                    /**
                     * Get Product Thumbnail
                     */
                    $productThumbnailUrl = $this->ordersHelper->getProductThumbnailUrl($item);

                    /**
                     * Get Magento Info
                     */
                    $platform = $this->ordersHelper->getPlatform();

                    /*
                    * Get Product Url
                    */

                    $productUrl = '';
                    if ($product) {
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
                    }

                    $ean = $this->ordersHelper->getProductEan($storeId, $item);

                    $row = [
                        $order->getId(),
                        $order->getBillingAddress()->getEmail(),
                        $order->getBillingAddress()->getEmail(),
                        $order->getCreatedAt(),
                        $productId,
                        str_replace('"', '""', $item->getName()),
                        $productUrl,
                        $productThumbnailUrl,
                        $ean,
                        $platform
                    ];

                    $data[] = $row;

                }
            }


        # END FORMAT DATA

        # WRITE TO CSV FILE

        $this->csv->saveData($outputFile, $data);
        $this->downloadCsv($outputFile);

        # END WRITE TO CSV

    }

    private function downloadCsv($file)
    {
        if (file_exists($file)) {
            //set headers
            header('Content-Description: File Transfer');
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            if (ob_get_contents() || ob_get_length()) ob_clean();
            flush();
            readfile($file);
        }
    }
}
