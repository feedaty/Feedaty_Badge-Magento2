<?php
namespace Feedaty\Badge\Controller\Adminhtml\Index;

use Feedaty\Badge\Helper\ConfigRules;
use Feedaty\Badge\Helper\Orders as OrdersHelper;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\File\Csv;
use \Magento\Framework\App\Filesystem\DirectoryList ;

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
    protected $_configRules;

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

    /*
    * Constructor
    *
    */
    private Context $context;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        PageFactory $resultPageFactory,
        ObjectManagerInterface $objectmanager,
        Csv $csv,
        DirectoryList $directoryList,
        OrdersHelper            $ordersHelper,
        ConfigRules             $configRules
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->_objectManager = $objectmanager;
        $this->_csv = $csv;
        $this->_directoryList = $directoryList;
        $this->ordersHelper = $ordersHelper;
        $this->context = $context;
        $this->_configRules = $configRules;
    }

    /*
    * Execute
    *
    */
    public function execute() {

        # INIT FIELDS

        $store_id = (int) $this->_request->getParam('store', 0);

        if ($store_id === 0)
        {
            $store_id = $this->_storeManager->getStore()->getId();
        }

        $scope_store = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $orderStatus = $this->_configRules->getSendOrderStatus($store_id);
        $fdDebugEnabled = $this->_configRules->getDebugModeEnabled($store_id);
        $exportDateFrom = $this->_configRules->getExportOrdersFrom($store_id);
        $exportDateTo = $this->_configRules->getExportOrdersTo($store_id);
        $last4months = date('Y-m-d', strtotime("-4 months"));
        $now = date('Y-m-d', strtotime("+1 days"));
        $from = $exportDateFrom != '' ? $exportDateFrom : $last4months;

        $to = $exportDateTo != '' ? $exportDateTo : $now;

        # END INIT FIELDS
        # DEBUG

        if($fdDebugEnabled != 0) {
            $message = "Status: ".$orderStatus." Store ID: ".$store_id;
            $feedatyHelper = $this->_objectManager->create('Feedaty\Badge\Helper\Data');
            $feedatyHelper->feedatyDebug($message, "FEEDATY CSV PARAMS");
        }

        # END DEBUG

        #  HANDLER

        $dirPath = $this->_directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $outputFile = $dirPath."/tmp/FeedatyOrderExport_". date('Ymd_His').".csv";

        if(!is_dir($dirPath))
            mkdir($dirPath, 0777, true);

        $orders = $this->_objectManager->create('\Magento\Sales\Model\Order')->getCollection()
            ->addFieldToFilter('status',$orderStatus)
            ->addFieldToFilter('store_id', $store_id)
            ->addAttributeToFilter('created_at', ['gteq'  => $from])
            ->addAttributeToFilter('created_at', ['lteq'  => $to]);

        $productMetadata = $this->_objectManager->get('Magento\Framework\App\ProductMetadataInterface');

        $delimiter = ',';
        $enclosure = '"';

        $heading = ["Order ID","UserID","E-mail","Date","Product ID","Extra","Product Url","Product Image","EAN","Platform"];

        $this->_csv->setDelimiter($delimiter);
        $this->_csv->setEnclosure($enclosure);

        #  END HANDLER

        #  FORMAT DATA

        $data[] = $heading;

        foreach ($orders as $order) {

            $objproducts = $order->getAllVisibleItems();

            foreach ($objproducts as $itemId => $item) {
                unset($tmp);
                if (!$item->getParentItem()) {

                    $fd_oProduct = $this->_objectManager->create('\Magento\Catalog\Model\Product')->load((int) $item->getProductId());

                    $tmp['Id'] = $item->getProductId();

                    $tmp['Url'] = $fd_oProduct->setStoreId($item->getStoreId())->getUrlInStore();

                    if ($fd_oProduct->getImage() != "no_selection")
                    {
                        // TODO: Use store manager
                        $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore($item->getStoreId());
                        $tmp['ImageUrl'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $fd_oProduct->getImage();
                    }
                    else
                    {
                        $tmp['ImageUrl'] = "";
                    }

                    $tmp['Name'] = $item->getName();
                    $tmp['Brand'] = $item->getBrand();
                    if ($tmp['Brand'] === null) $tmp['Brand']  = "";

                    $ean = $this->ordersHelper->getProductEan($store_id, $item);

                    $row = [
                        $order->getId(),
                        $order->getBillingAddress()->getEmail(),
                        $order->getBillingAddress()->getEmail(),
                        $order->getCreatedAt(),
                        $item->getProductId(),
                        str_replace('"','""',$tmp['Name']),
                        $tmp['Url'],
                        $tmp['ImageUrl'],
                        $ean,
                        "Magento".$productMetadata->getVersion()."CSV"
                    ];

                     $data[] = $row;

                }
            }

        }

        # END FORMAT DATA

        # WRITE TO CSV FILE

        $this->_csv->saveData($outputFile, $data);
        $this->downloadCsv($outputFile);

        # END WRITE TO CSV

    }

    private function downloadCsv($file) {
        if (file_exists($file)) {
            //set headers
            header('Content-Description: File Transfer');
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename='.basename($file));
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
